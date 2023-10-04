export class GameMenu {
    #setting
    #hud
    #element

    constructor(element, setting, hud) {
        this.#setting = setting
        this.#hud = hud
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
                <button data-close>Close</button>
            </div>
        `;
        this.#element = element.querySelector('[data-setting]')
        element.addEventListener('keydown', (e) => e.target.matches('input,textarea') && e.stopPropagation())
        element.addEventListener('keyup', (e) => e.target.matches('input,textarea') && e.stopPropagation())
        element.querySelector('[data-save]').addEventListener('click', () => this.#saveSetting())
        element.querySelector('[data-close]').addEventListener('click', () => this.#hud.toggleGameMenu())
    }

    #saveSetting() {
        const setting = this.#setting
        setting.update('sensitivity', parseFloat(this.#element.querySelector('input[name="sensitivity"]').value))
        setting.update('inScopeSensitivity', parseFloat(this.#element.querySelector('input[name="sensitivity-scope"]').value))
        setting.update('radarZoom', parseFloat(this.#element.querySelector('input[name="radar-zoom"]').value))
        setting.update('volume', parseFloat(this.#element.querySelector('input[name="volume"]').value))
        setting.update('crosshairColor', '' + this.#element.querySelector('input[name="crosshair-color"]').value)
        setting.update('crosshairSize', parseInt(this.#element.querySelector('input[name="crosshair-size"]').value))
        setting.update('hudColor', '' + this.#element.querySelector('input[name="hud-color"]').value)
        setting.update('hudColorShadow', '' + this.#element.querySelector('input[name="hud-color-shadow"]').value)

        const json = setting.getJson()
        setting.loadSettings(json)
        window.localStorage.setItem('setting', json)
    }

    show() {
        if (this.#element.innerHTML !== '') {
            return;
        }

        this.#element.innerHTML = `
            <div class="row">
                <div>
                    <p>Mouse sensitivity:</p>
                    <p><input name="sensitivity" type="number" min="0.1" max="99" step="0.1" value="${this.#setting.getSensitivity()}"></p>
                </div>
                <div>
                    <p>Mouse scope sensitivity:</p>
                    <p><input name="sensitivity-scope" type="number" min="0.1" max="99" step="0.1" value="${this.#setting.getInScopeSensitivity()}"></p>
                </div>
            </div>
             <div class="row">
                <div>
                    <p>Radar zoom:</p>
                    <p><input name="radar-zoom" type="number" min="0.1" max="99" step="0.1" value="${this.#setting.getRadarZoom()}"></p>
                </div>
                <div>
                    <p>Master volume:</p>
                    <p><input name="volume" type="number" min="0" max="100" step="1" value="${this.#setting.getMasterVolume()}"></p>
                </div>
            </div>
            <div class="row">
                <div>
                    <p>Crosshair size:</p>
                    <p><input name="crosshair-size" type="number" min="1" max="200" step="1" value="${this.#setting.getCrosshairSize()}"></p>
                </div>
                <div>
                    <p>Crosshair color:</p>
                    <p><input name="crosshair-color" type="color" value="${this.#setting.getCrosshairColor()}"></p>
                </div>
            </div>
            <div class="row">
                <div>
                    <p>Hud color:</p>
                    <p><input name="hud-color" type="color" value="${this.#setting.getHudColor()}"></p>
                </div>
                <div>
                    <p>Hud shadow color:</p>
                    <p><input name="hud-color-shadow" type="color" value="${this.#setting.getHudColorShadow()}"></p>
                </div>
            </div>
        `;
    }

    close() {
        this.#element.innerHTML = ''
    }

}
