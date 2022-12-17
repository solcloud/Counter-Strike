export class GameMenu {
    #element
    #setting

    constructor(element, setting) {
        this.#setting = setting
        this.#init(element)
    }

    #init(element) {
        element.innerHTML = `
            <div data-setting></div>
            <div>
                <button data-save>Save settings</button>
            </div>
            <hr>
            <div class="flex">
                <button onclick="window.location.reload()">Disconnect</button>
                <span>&nbsp;</span>
                <button onclick="window.close()">Quit game</button>
            </div>
        `;
        this.#element = element.querySelector('[data-setting]')
        element.addEventListener('keydown', (e) => e.target.matches('input,textarea') && e.stopPropagation())
        element.addEventListener('keyup', (e) => e.target.matches('input,textarea') && e.stopPropagation())
        element.querySelector('[data-save]').addEventListener('click', () => this.#saveSetting())
    }

    #saveSetting() {
        const setting = this.#setting
        setting.update('sensitivity', parseFloat(this.#element.querySelector('input[name="sensitivity"]').value))
        setting.update('volume', parseFloat(this.#element.querySelector('input[name="volume"]').value))
        setting.update('crosshairColor', this.#element.querySelector('input[name="crosshair-color"]').value.substring(1))

        const json = setting.getJson()
        setting.loadSettings(json)
        window.localStorage.setItem('setting', json)
    }

    show() {
        if (this.#element.innerHTML !== '') {
            return;
        }

        this.#element.innerHTML = `
            <div>
                <p>Mouse sensitivity:</p>
                <p><input name="sensitivity" type="number" min="0.1" max="99" step="0.1" value="${this.#setting.getSensitivity()}"></p>
            </div>
            <div>
                <p>Master volume:</p>
                <p><input name="volume" type="number" min="0" max="100" step="1" value="${this.#setting.getMasterVolume()}"></p>
            </div>
            <div>
                <p>Crosshair color:</p>
                <p><input name="crosshair-color" type="color" value="#${this.#setting.getCrosshairColor()}"></p>
            </div>
        `;
    }

    close() {
        this.#element.innerHTML = ''
    }

}
