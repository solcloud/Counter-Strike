import {Game} from "./Game.js";
import {HUD} from "./Hud.js";
import {Control} from "./Control.js";
import {World} from "./World.js";
import {WebSocketConnector} from "./WebSocketConnector.js";
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
    hud.setGame(game)

////////////


    launchGame = async function (canvasParent, elementHud, setting) {
        if (initialized) {
            throw new Error("Game already launched")
        }

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
        canvasParent.appendChild(stats.dom);

        let connector = new WebSocketConnector(game)
        game.onEnd(function (msg) {
            connector.close()
            alert("Game ended: " + msg)
        })
        game.onReady(function (options) {
            connector.startLoop(control, options.tickMs)
        })
        connector.connect(setting.url, setting.code)
    }

})()

export {
    launchGame
}
