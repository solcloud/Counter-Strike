import {Color, InventorySlot} from "../Enums.js";

export class Radar {
    #image
    #canvas
    #ctx
    #players
    #mapObjects
    #rayCaster
    #camera
    #frustum
    #spottedPlayerIds = {}
    #scaleX
    #scaleY
    #zoom
    #mapSize
    #mapCenterX
    #mapCenterY
    #padding
    #mapSizes = {
        "default": {x: 3440, y: 2560},
    }

    constructor(canvas, mapImage, map, game, zoom = 0.9) {
        const mapSize = this.#mapSizes[map]
        if (!mapSize) {
            throw new Error("Unknown size for map: " + map)
        }

        this.#mapSize = mapSize
        this.#image = mapImage
        this.#canvas = canvas
        this.#ctx = this.#canvas.getContext('2d')

        this.#players = game.players
        this.#mapObjects = game.getMapObjects()
        this.#rayCaster = new THREE.Raycaster()
        this.#camera = new THREE.PerspectiveCamera(70, 2, 1, 9999)
        this.#camera.rotation.reorder('YXZ')
        this.#camera.matrixAutoUpdate = false
        this.#frustum = new THREE.Frustum()

        this.#mapCenterX = Math.round(mapSize.x / 2)
        this.#mapCenterY = Math.round(mapSize.y / 2)
        this.#scaleX = Math.round(canvas.width / mapSize.x * 1000) / 1000
        this.#scaleY = Math.round(canvas.height / mapSize.y * 1000) / 1000
        this.setZoom(zoom)
    }

    setZoom(zoom) {
        this.#zoom = zoom

        this.#padding = {x: {min: 0, max: this.#mapSize.x}, y: {min: 0, max: this.#mapSize.y}}
        if (zoom < 1.4) { // fixme do proportion based valued on some fixed map size like 1000 against actual map size
            const cornerPaddingDivider = 3
            this.#padding.x.min = Math.ceil(this.#mapSize.x / cornerPaddingDivider)
            this.#padding.x.max = this.#mapSize.x - this.#padding.x.min
            this.#padding.y.min = Math.ceil(this.#mapSize.y / cornerPaddingDivider)
            this.#padding.y.max = this.#mapSize.y - this.#padding.y.min
        }
    }

    update(myTeamPlayers, idSpectator, spectatorRotationHorizontal, bombPosition) {
        let spectator
        let bombHasPlayer = false
        const ctx = this.#ctx
        this.#spottedPlayerIds = {}
        ctx.resetTransform()
        ctx.drawImage(this.#image, 0, 0, this.#canvas.width, this.#canvas.height)

        ctx.translate(0, this.#canvas.height)
        ctx.scale(this.#scaleX, this.#scaleY)
        myTeamPlayers.forEach((player) => {
            if (!player.isAlive()) {
                return
            }
            if (player.getId() === idSpectator) {
                spectator = player
                ctx.lineWidth = 12
                ctx.strokeStyle = "#462b02"
            } else {
                ctx.lineWidth = 10
                ctx.strokeStyle = "#70a670"
            }
            ctx.fillStyle = "#" + Color[player.data.color].toString(16).padStart(6, '0')

            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 60, 0, 2 * Math.PI)
            ctx.fill()

            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 62, 0, 2 * Math.PI)
            ctx.stroke()

            if (undefined !== player.data.slots[InventorySlot.SLOT_BOMB]) {
                bombHasPlayer = true
                bombPosition = player.data.position
            }
            this.#checkEnemySpotted(player, ctx)
        })
        if (bombPosition) {
            ctx.beginPath()
            ctx.fillStyle = "#FF1100"
            if (bombHasPlayer) {
                ctx.arc(bombPosition.x + 55, -bombPosition.z + 55, 42, 0, 2 * Math.PI)
            } else {
                ctx.arc(bombPosition.x, -bombPosition.z, 42, 0, 2 * Math.PI)
            }
            ctx.fill()
        }

        if (!spectator) {
            return
        }

        const meX = Math.min(Math.max(this.#padding.x.min, spectator.data.position.x), this.#padding.x.max)
        const meY = Math.min(Math.max(this.#padding.y.min, spectator.data.position.z), this.#padding.y.max)
        const centerX = (meX > this.#mapCenterX ? -(meX - this.#mapCenterX) : this.#mapCenterX - meX)
        const centerY = (meY > this.#mapCenterY ? -(meY - this.#mapCenterY) : this.#mapCenterY - meY)
        ctx.resetTransform()
        ctx.globalCompositeOperation = "copy"
        ctx.translate(this.#canvas.width / 2, this.#canvas.height / 2)
        ctx.rotate(-spectatorRotationHorizontal * Math.PI / 180)
        ctx.scale(this.#zoom, this.#zoom)
        ctx.translate(this.#canvas.width / -2, this.#canvas.height / -2)
        ctx.drawImage(ctx.canvas, centerX * this.#scaleX, -centerY * this.#scaleY)
        ctx.globalCompositeOperation = "source-over"
    }

    #checkEnemySpotted(myTeamPlayer, ctx) {
        const meIsAttacker = myTeamPlayer.isAttacker()
        const matePosition = myTeamPlayer.getSightPositionThreeVector()
        this.#camera.position.set(matePosition.x, matePosition.y, matePosition.z)
        this.#camera.rotation.set(serverVerticalRotationToThreeRadian(myTeamPlayer.data.look.vertical), serverHorizontalRotationToThreeRadian(myTeamPlayer.data.look.horizontal), 0)
        this.#camera.zoom = scopeLevelToZoom(myTeamPlayer.data.scopeLevel)
        this.#camera.aspect = (myTeamPlayer.data.scopeLevel === 0) ? 2 : 1
        this.#camera.updateMatrix()
        this.#camera.updateMatrixWorld()
        this.#camera.updateProjectionMatrix()
        this.#frustum.setFromProjectionMatrix(new THREE.Matrix4().multiplyMatrices(this.#camera.projectionMatrix, this.#camera.matrixWorldInverse))

        this.#players.forEach((player) => {
            if (!player.isAlive() || player.isAttacker() === meIsAttacker || this.#spottedPlayerIds[player.getId()]) {
                return
            }
            if (!this.#frustum.containsPoint(player.getSightPositionThreeVector())) {
                return
            }

            this.#rayCaster.set(matePosition, player.getSightPositionThreeVector().sub(matePosition).normalize())
            const intersects = this.#rayCaster.intersectObjects(this.#mapObjects, false)
            if (intersects.length === 0 || intersects[0].distance < matePosition.distanceTo(player.getSightPositionThreeVector())) {
                return
            }

            ctx.fillStyle = "#FF0000"
            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 60, 0, 2 * Math.PI)
            ctx.fill()
        })
    }

}
