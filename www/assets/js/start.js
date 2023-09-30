import {Game} from "./Game.js";
import {HUD} from "./Hud.js";
import {Control} from "./Control.js";
import {World} from "./World.js";
import Stats from "../threejs/Stats.js";
import {Setting} from "./Setting.js";
import {PlayerAction} from "./PlayerAction.js";

let launchGame
(function () {

////////////

    const validMaps = ['default']
    let initialized = false, statsLocal = null
    const world = new World()
    const hud = new HUD()
    const stats = new Stats()
    const game = new Game(world, hud, stats)
    const action = new PlayerAction(game, hud)
    const control = new Control(game, action)
    hud.injectDependency(game)

////////////


    launchGame = async function (canvasParent, elementHud, settingString, joinUrl) {
        if (initialized) {
            throw new Error("Game already launched")
        }
        initialized = true

        let connector
        let url = new URL(joinUrl)
        if (url.protocol === 'ws:') {
            const ns = await import("./WebSocketConnector.js")
            connector = new ns.WebSocketConnector(game)
        } else if (url.protocol === 'udp:') {
            const ns = await import("./UdpSocketConnector.js")
            connector = new ns.UdpSocketConnector(game)
            url = new URL(joinUrl.replace('udp://', 'https://')) // URL do not parse udp parts well, so do https instead
        } else {
            throw new Error('Unknown protocol given')
        }

        const loginCode = url.searchParams.get('code')
        const map = url.searchParams.get('map')
        if (!validMaps.includes(map)) {
            throw new Error("Invalid map given")
        }

        const setting = new Setting(settingString)
        const canvas = await world.init(map, setting)
        const pointerLock = new THREE.PointerLockControls(world.getCamera(), canvasParent)
        pointerLock.pointerSpeed = setting.getSensitivity()

        hud.createHud(elementHud, map, setting)
        control.init(canvasParent, pointerLock, setting)
        game.setDependency(pointerLock, setting)
        canvas.addEventListener("click", () => game.requestPointerLock())
        canvasParent.appendChild(canvas)
        if (setting.shouldShowFps()) {
            stats.dom.style.position = 'inherit'
            elementHud.querySelector('#fps-stats').appendChild(stats.dom)
        }

        game.onEnd(function (msg) {
            connector.close()
            hud.showScore()
            alert("Game ended: " + msg)
            window.location.reload()
        })
        game.onReady(function () {
            if (!setting.shouldMatchServerFps()) {
                if (setting.shouldShowFps()) {
                    statsLocal = new Stats()
                    statsLocal.dom.style.position = 'inherit'
                    elementHud.querySelector('#fps-stats').appendChild(statsLocal.dom)
                }
                render()
            }
        })

        setting.addUpdateCallback('sensitivity', (newValue) => pointerLock.pointerSpeed = parseFloat(newValue))
        setting.addUpdateCallback('volume', (newValue) => world.volume = parseFloat(newValue))
        connector.connect(url.hostname, url.port, loginCode, control)
        window.addEventListener('beforeunload', () => connector.close());
    }

    function render() {
        statsLocal && statsLocal.begin()
        world.render()
        statsLocal && statsLocal.end()
        requestAnimationFrame(render)
    }

})()

export {
    launchGame
}
