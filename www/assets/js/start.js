import {Game} from "./Game.js";
import {HUD} from "./Hud.js";
import {Control} from "./Control.js";
import {World} from "./World.js";
import {WebSocketConnector} from "./WebSocketConnector.js";

let launchGame
(function () {

////////////

    let initialized = false
    const world = new World()
    const hud = new HUD()
    const game = new Game(world, hud);
    const control = new Control(game, hud)
    hud.setGame(game)

////////////


    launchGame = async function (canvasParent, elementHud, map, address, code) {
        if (initialized) {
            throw new Error("Game already launched")
        }

        initialized = true
        const canvas = await world.init(map)
        hud.createHud(elementHud)
        control.init(world.getCamera())
        document.addEventListener("click", () => control.requestLock())
        canvasParent.appendChild(canvas)

        let connector = new WebSocketConnector(game)
        game.onEnd(function (msg) {
            connector.close()
            alert("Game ended: " + msg)
        })
        game.onReady(function (options) {
            connector.startLoop(control, options.tickMs)
        })
        connector.connect(address, code)
    }

})()

export {
    launchGame
}
