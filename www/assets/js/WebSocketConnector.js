export class WebSocketConnector {
    #game;
    #socket;
    #sendIntervalId;

    constructor(game) {
        this.#game = game
    }

    close() {
        clearInterval(this.#sendIntervalId)
        this.#socket.send('CLOSE')
        this.#socket.close()
    }

    connect(host, port, loginCode) {
        let logged = false;

        const socket = new WebSocket(`ws://${host}:${port}`);
        this.#socket = socket

        const connector = this
        const game = this.#game
        socket.onclose = function () {
            clearInterval(connector.#sendIntervalId)
            console.log("WebSocket closed")
        };
        socket.onerror = function (error) {
            alert(`Cannot connect to '${host}:${port}'`)
            console.log("WebSocket error: " + error.message)
        };
        socket.onopen = function () {
            console.log("WebSocket connection established.")
            if (!logged) {
                console.log("Sending login code to server.")
                socket.send("login " + loginCode)
                logged = true
            }
        };
        socket.onmessage = function (event) {
            let state
            try {
                state = JSON.parse(event.data)
            } catch (err) {
                game.end("Message parse error! " + err.message)
                return
            }
            game.tick(state)
        };

    }

    startLoop(control, tickMs) {
        const socket = this.#socket

        this.#sendIntervalId = setInterval(function () {
            socket.send(control.getTickAction())
        }, tickMs)
    }
}
