import {Color, InventorySlot} from "../Enums.js";

export class Radar {
    #image
    #canvas
    #ctx
    #scaleX
    #scaleY
    #zoom
    #mapCenterX
    #mapCenterY
    #padding
    #mapSizes = {
        "default": {x: 3440, y: 2560},
        "aim": {x: 2000, y: 2000},
    }

    constructor(canvas, mapImage, map, zoom = 0.9) {
        const mapSize = this.#mapSizes[map]
        if (!mapSize) {
            throw new Error("Unknown size for map: " + map)
        }

        this.#image = mapImage
        this.#canvas = canvas
        this.#ctx = this.#canvas.getContext('2d')

        this.#mapCenterX = Math.round(mapSize.x / 2)
        this.#mapCenterY = Math.round(mapSize.y / 2)
        this.#scaleX = Math.round(canvas.width / mapSize.x * 1000) / 1000
        this.#scaleY = Math.round(canvas.height / mapSize.y * 1000) / 1000
        this.#zoom = zoom

        this.#padding = {x: {min: 0, max: mapSize.x}, y: {min: 0, max: mapSize.y}}
        if (zoom < 1.5) {
            const cornerPaddingDivider = 3
            this.#padding.x.min = Math.ceil(mapSize.x / cornerPaddingDivider)
            this.#padding.x.max = mapSize.x - this.#padding.x.min
            this.#padding.y.min = Math.ceil(mapSize.y / cornerPaddingDivider)
            this.#padding.y.max = mapSize.y - this.#padding.y.min
        }
    }

    update(players, idSpectator, spectatorRotationHorizontal, bombPosition) {
        let spectator
        const ctx = this.#ctx
        ctx.resetTransform()
        ctx.drawImage(this.#image, 0, 0, this.#canvas.width, this.#canvas.height)

        ctx.translate(0, this.#canvas.height)
        ctx.scale(this.#scaleX, this.#scaleY)
        players.forEach(function (player) {
            if (!player.isAlive()) {
                return
            }
            if (player.getId() === idSpectator) {
                spectator = player
                ctx.lineWidth = 12;
                ctx.strokeStyle = "#462b02"
            } else {
                ctx.lineWidth = 10;
                ctx.strokeStyle = "#70a670"
            }
            ctx.fillStyle = "#" + Color[player.data.color].toString(16).padStart(6, '0');

            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 60, 0, 2 * Math.PI)
            ctx.fill()

            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 62, 0, 2 * Math.PI)
            ctx.stroke()

            if (undefined !== player.data.slots[InventorySlot.SLOT_BOMB]) {
                bombPosition = player.data.position
            }
        })
        if (bombPosition) {
            ctx.beginPath()
            ctx.fillStyle = "#FF1100";
            ctx.arc(bombPosition.x + 55, -bombPosition.z + 55, 42, 0, 2 * Math.PI)
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
        ctx.globalCompositeOperation = "copy";
        ctx.translate(this.#canvas.width / 2, this.#canvas.height / 2)
        ctx.rotate(-spectatorRotationHorizontal * Math.PI / 180)
        ctx.scale(this.#zoom, this.#zoom)
        ctx.translate(this.#canvas.width / -2, this.#canvas.height / -2)
        ctx.drawImage(ctx.canvas, centerX * this.#scaleX, -centerY * this.#scaleY);
        ctx.globalCompositeOperation = "source-over"
    }

}
