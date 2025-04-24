import * as THREE from 'three'
import {Color, InventorySlot} from "../Enums.js";
import {Utils} from "../Utils.js";

export class Radar {
    #game
    #image
    #canvas
    #ctx
    #rayCaster
    #camera
    #frustum
    #spottedPlayerIds = {}
    #scale
    #scaleFont
    #zoom
    #keepCentered
    #mapSize
    #mapCenter
    #borderPadding
    #mapSizes = {
        "default": {
            "radarImgBoundary": 15000,
            "boundary": {
               "min": [1155, 1030],
               "max": [13190, 13490],
            },
        },
    }

    constructor(canvas, mapImage, map, game) {
        const mapSize = this.#mapSizes[map]
        if (!mapSize) {
            throw new Error("Unknown data for map: " + map)
        }

        this.#game = game
        this.#mapSize = mapSize
        this.#image = mapImage
        this.#canvas = canvas
        this.#ctx = this.#canvas.getContext('2d')

        this.#rayCaster = new THREE.Raycaster()
        this.#rayCaster.layers.set(Utils.LAYER_WORLD)
        this.#rayCaster.layers.enable(Utils.LAYER_PLAYERS)
        this.#camera = new THREE.PerspectiveCamera(70, 2, 1, 99999)
        this.#camera.rotation.reorder('YXZ')
        this.#camera.matrixAutoUpdate = false
        this.#frustum = new THREE.Frustum()

        this.#mapCenter = Math.round(mapSize.radarImgBoundary / 2)
        this.#scale = Math.round(canvas.width / mapSize.radarImgBoundary * 1000) / 1000
        this.#scaleFont = Math.ceil(this.#scale * 30)
    }

    setZoom(zoom, keepCentered) {
        this.#zoom = zoom
        this.#keepCentered = keepCentered
        this.#borderPadding = keepCentered || zoom > 3 ? 0 : Math.ceil(
            (4 - Math.min(3, zoom)) * (this.#mapSize.radarImgBoundary / 11)
        );
    }

    update(myTeamPlayers, idSpectator, spectatorRotationHorizontal, bombPosition) {
        let spectator
        let bombHasPlayer = false
        const ctx = this.#ctx
        this.#spottedPlayerIds = {}
        ctx.resetTransform()
        ctx.drawImage(this.#image, 0, 0, this.#canvas.width, this.#canvas.height)

        ctx.translate(0, this.#canvas.height)
        ctx.scale(this.#scale, this.#scale)
        myTeamPlayers.forEach((player) => {
            if (!player.isAlive()) {
                return
            }
            if (player.getId() === idSpectator) {
                spectator = player
                ctx.lineWidth = 12 * this.#scaleFont
                ctx.strokeStyle = "#462b02"
            } else {
                ctx.lineWidth = 10 * this.#scaleFont
                ctx.strokeStyle = "#70a670"
            }
            ctx.fillStyle = "#" + Color[player.data.color].toString(16).padStart(6, '0')

            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 60 * this.#scaleFont, 0, 2 * Math.PI)
            ctx.fill()

            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 62 * this.#scaleFont, 0, 2 * Math.PI)
            ctx.stroke()

            if (undefined !== player.data.slots[InventorySlot.SLOT_BOMB]) {
                bombHasPlayer = true
                bombPosition = player.data.position
                bombPosition.x += 55
                bombPosition.z += 55
            }
            this.#checkEnemySpotted(player, ctx)
        })
        if (bombPosition) {
            ctx.beginPath()
            ctx.fillStyle = "#FF1100"
            ctx.arc(bombPosition.x, -bombPosition.z, 42 * this.#scaleFont, 0, 2 * Math.PI)
            ctx.fill()
        }

        if (!spectator) {
            return
        }

        const meX = Math.min(Math.max(this.#mapSize.boundary.min[0] + this.#borderPadding, spectator.data.position.x), this.#mapSize.boundary.max[0] - this.#borderPadding)
        const meY = Math.min(Math.max(this.#mapSize.boundary.min[1] + this.#borderPadding, spectator.data.position.z), this.#mapSize.boundary.max[1] - this.#borderPadding)
        const centerX = (meX > this.#mapCenter ? -(meX - this.#mapCenter) : this.#mapCenter - meX)
        const centerY = (meY > this.#mapCenter ? -(meY - this.#mapCenter) : this.#mapCenter - meY)
        ctx.resetTransform()
        ctx.globalCompositeOperation = "copy"
        ctx.translate(this.#canvas.width / 2, this.#canvas.height / 2)
        ctx.rotate(-spectatorRotationHorizontal * Math.PI / 180)
        ctx.scale(this.#zoom, this.#zoom)
        ctx.translate(this.#canvas.width / -2, this.#canvas.height / -2)
        ctx.drawImage(ctx.canvas, centerX * this.#scale, -centerY * this.#scale)
        ctx.globalCompositeOperation = "source-over"
    }

    #checkEnemySpotted(myTeamPlayer, ctx) {
        const meIsAttacker = myTeamPlayer.isAttacker()
        const matePosition = myTeamPlayer.getSightPositionThreeVector()
        this.#camera.position.set(matePosition.x, matePosition.y, matePosition.z)
        this.#camera.rotation.set(Utils.serverVerticalRotationToThreeRadian(myTeamPlayer.data.look.vertical), Utils.serverHorizontalRotationToThreeRadian(myTeamPlayer.data.look.horizontal), 0)
        this.#camera.zoom = Utils.scopeLevelToZoom(myTeamPlayer.data.scopeLevel)
        this.#camera.aspect = (myTeamPlayer.data.scopeLevel === 0) ? 2 : 1
        this.#camera.updateMatrix()
        this.#camera.updateMatrixWorld()
        this.#camera.updateProjectionMatrix()
        this.#frustum.setFromProjectionMatrix(new THREE.Matrix4().multiplyMatrices(this.#camera.projectionMatrix, this.#camera.matrixWorldInverse))

        this.#game.players.forEach((player) => {
            if (!player.isAlive() || player.isAttacker() === meIsAttacker || this.#spottedPlayerIds[player.getId()]) {
                return
            }
            if (!this.#frustum.containsPoint(player.getSightPositionThreeVector())) {
                return
            }

            this.#rayCaster.set(matePosition, player.getSightPositionThreeVector().sub(matePosition).normalize())
            const intersects = this.#rayCaster.intersectObject(this.#game.getScene())
            if (intersects.length === 0 || intersects[0].distance + 50 < matePosition.distanceTo(player.getSightPositionThreeVector())) {
                return
            }
            if (!intersects[0].object.layers.isEnabled(Utils.LAYER_PLAYERS)) {
                return;
            }

            ctx.fillStyle = "#FF0000"
            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 60 * this.#scaleFont, 0, 2 * Math.PI)
            ctx.fill()
            this.#spottedPlayerIds[player.getId()] = true
        })
    }

}
