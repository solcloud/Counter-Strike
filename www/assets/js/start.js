import {Game} from "./Game.js";
import {HUD} from "./Hud.js";
import {Control} from "./Control.js";
import {World} from "./World.js";
import Stats from "./Stats.js";

let launchGame
(function () {

////////////

    let initialized = false
    const world = new World()
    const hud = new HUD()
    const stats = new Stats();
    const game = new Game(world, hud, stats);
    const control = new Control(game, hud)
    hud.injectDependency(game, control)

////////////


    launchGame = async function (canvasParent, elementHud, setting) {
        if (initialized) {
            throw new Error("Game already launched")
        }

        let connector
        initialized = true
        const canvas = await world.init(setting.map, setting.world)
        hud.createHud(elementHud)
        control.init(world.getCamera())
        document.addEventListener("click", function (e) {
            if (e.target.classList.contains('hud-action')) {
                return
            }
            control.requestLock()
        }, {capture: true})
        canvasParent.appendChild(canvas)
        canvasParent.appendChild(stats.dom)

        game.onEnd(function (msg) {
            connector.close()
            alert("Game ended: " + msg)
            window.location.reload()
        })
        game.onReady(function (options) {
            connector.startLoop(control, options.tickMs)
        })

        const url = new URL(setting.url)
        if (url.protocol === 'ws:') {
            const ns = await import("./WebSocketConnector.js")
            connector = new ns.WebSocketConnector(game)
            connector.connect(setting.url, setting.code)
        } else if (url.protocol === 'udp:') {
            const ns = await import("./UdpSocketConnector.js")
            connector = new ns.UdpSocketConnector(game)
            let url = new URL(setting.url.replace('udp://', 'http://')) // URL do not parse udp parts well, so do http instead
            connector.connect(url.hostname, url.port, setting.code)
        } else {
            alert('Unknown protocol given')
            return
        }
    }

})()

export {
    launchGame
}
