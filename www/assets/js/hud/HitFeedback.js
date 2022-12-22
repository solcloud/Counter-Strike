export class HitFeedback {
    #left = 0
    #right = 0
    #front = 0
    #back = 0
    #elements = {
        left: null,
        right: null,
        front: null,
        back: null,
    }
    #intervalId = null

    constructor(element) {
        this.#init(element)
    }

    #init(element) {
        element.innerHTML = `
            <div data-left style="opacity:0">❮</div>
            <div style="flex-grow:1">
                <div data-front style="opacity:0;rotate:90deg">❮</div>
                <div data-back style="opacity:0;rotate:-90deg">❮</div>
            </div>
            <div data-right style="opacity:0;right:0">❯</div>
        `;
        this.#elements.left = element.querySelector('[data-left]')
        this.#elements.right = element.querySelector('[data-right]')
        this.#elements.front = element.querySelector('[data-front]')
        this.#elements.back = element.querySelector('[data-back]')
    }

    hit(fromLeft, fromRight, fromFront, fromBack) {
        if (fromLeft) {
            this.#left = 100
        }
        if (fromRight) {
            this.#right = 100
        }
        if (fromFront) {
            this.#front = 100
        }
        if (fromBack) {
            this.#back = 100
        }

        if (this.#intervalId !== null) {
            return
        }
        let intervalId = setInterval(() => {
            this.#left = Math.max(0, this.#left - 10)
            this.#right = Math.max(0, this.#right - 10)
            this.#front = Math.max(0, this.#front - 10)
            this.#back = Math.max(0, this.#back - 10)

            this.#elements.left.style.opacity = this.#left / 100
            this.#elements.right.style.opacity = this.#right / 100
            this.#elements.front.style.opacity = this.#front / 100
            this.#elements.back.style.opacity = this.#back / 100

            if (this.#left === 0 && this.#right === 0 && this.#front === 0 && this.#back === 0) {
                clearInterval(intervalId)
                this.#intervalId = null
            }
        }, 100)
        this.#intervalId = intervalId
    }
}
