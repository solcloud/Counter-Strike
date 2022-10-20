import {Color} from "../Enums.js";

export class Radar {
    #image
    #canvas
    #ctx
    #scaleX
    #scaleY
    #mapCenterX
    #mapCenterY
    #mapSizes = {
        "default": {x: 3440, y: 2560},
        "aim": {x: 2000, y: 2000},
    }

    constructor(canvas, mapImage, map) {
        const mapSize = this.#mapSizes[map]
        if (!mapSize) {
            throw new Error("Uknown size for map: " + map)
        }

        this.#image = mapImage
        this.#canvas = canvas
        this.#ctx = this.#canvas.getContext('2d')

        this.#mapCenterX = Math.round(mapSize.x / 2)
        this.#mapCenterY = Math.round(mapSize.y / 2)
        this.#scaleX = Math.round(canvas.width / mapSize.x * 100) / 100
        this.#scaleY = Math.round(canvas.height / mapSize.y * 100) / 100
    }

    update(players, idMe, rotationHorizontalMe) {
        let playerMe
        const ctx = this.#ctx
        ctx.resetTransform()
        ctx.drawImage(this.#image, 0, 0, this.#canvas.width, this.#canvas.height)

        ctx.translate(0, this.#canvas.height)
        ctx.scale(this.#scaleX, this.#scaleY)
        players.forEach(function (player) {
            if (player.getId() === idMe) {
                playerMe = player
                ctx.fillStyle = "#d9d9d9"
                ctx.lineWidth = 10;
                ctx.strokeStyle = "#462b02"
            } else {
                ctx.lineWidth = 8;
                ctx.strokeStyle = "#70a670"
                ctx.fillStyle = Color[player.getId()]
            }

            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 50, 0, 2 * Math.PI)
            ctx.fill()

            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 51, 0, 2 * Math.PI)
            ctx.stroke()
        })

        const meX = playerMe.data.position.x
        const meY = playerMe.data.position.z
        const centerX = (meX > this.#mapCenterX ? -(meX - this.#mapCenterX) : this.#mapCenterX - meX)
        const centerY = (meY > this.#mapCenterY ? -(meY - this.#mapCenterY) : this.#mapCenterY - meY)
        ctx.resetTransform()
        ctx.globalCompositeOperation = "copy";
        ctx.drawImage(ctx.canvas, centerX * this.#scaleX, -centerY * this.#scaleY);

        const rotation = -rotationHorizontalMe
        ctx.translate(this.#canvas.width / 2, this.#canvas.height / 2)
        ctx.rotate(rotation * Math.PI / 180)
        ctx.translate(this.#canvas.width / -2, this.#canvas.height / -2)
        ctx.drawImage(ctx.canvas, 0, 0)
        ctx.globalCompositeOperation = "source-over"
    }

}
