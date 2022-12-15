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
    let initialized = false
    const world = new World()
    const hud = new HUD()
    const stats = new Stats()
    const game = new Game(world, hud, stats)
    const action = new PlayerAction(hud)
    const control = new Control(game, action)
    let statsLocal
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
        const pointerLock = new THREE.PointerLockControls(world.getCamera(), document.body)
        pointerLock.pointerSpeed = setting.getSensitivity()
        hud.createHud(elementHud, map, setting)
        control.init(pointerLock, setting)
        game.setDependency(pointerLock, setting.shouldMatchServerFps())
        document.addEventListener("click", function (e) {
            if (e.target.classList.contains('hud-action')) {
                return
            }
            game.requestPointerLock()
        }, {capture: true})
        canvasParent.appendChild(canvas)
        stats.dom.style.position = 'inherit'
        elementHud.querySelector('#fps-stats').appendChild(stats.dom)

        game.onEnd(function (msg) {
            connector.close()
            hud.showScore()
            alert("Game ended: " + msg)
            window.location.reload()
        })
        game.onReady(function (options) {
            connector.startLoop(control, options.tickMs)
            if (!setting.shouldMatchServerFps()) {
                statsLocal = new Stats()
                statsLocal.dom.style.position = 'inherit'
                elementHud.querySelector('#fps-stats').appendChild(statsLocal.dom)
                render()
            }
        })

        connector.connect(url.hostname, url.port, loginCode)
    }

    function render() {
        statsLocal.begin()
        world.render()
        statsLocal.end()
        requestAnimationFrame(render)
    }

})()

export {
    launchGame
}
