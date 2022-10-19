export class Radar {
    #image
    #canvas
    #ctx
    #scaleX
    #scaleY

    constructor(canvas, mapImage, scaleX, scaleY) {
        this.#image = mapImage
        this.#canvas = canvas
        this.#ctx = this.#canvas.getContext('2d')
        this.#scaleX = scaleX
        this.#scaleY = scaleY
    }

    update(players, idMe) {
        const ctx = this.#ctx
        ctx.resetTransform()
        ctx.drawImage(this.#image, 0, 0, this.#canvas.width, this.#canvas.height)

        ctx.translate(0, this.#canvas.height)
        ctx.scale(this.#scaleX, this.#scaleY)
        players.forEach(function (player) {
            ctx.fillStyle = player.getId() === idMe ? "#28c9a3" : "#70a670"
            ctx.beginPath()
            ctx.arc(player.data.position.x, -player.data.position.z, 50, 0, 2 * Math.PI)
            ctx.fill()
        })
    }

}
