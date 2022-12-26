export class WebSocketConnector {
    #game;
    #socket;

    constructor(game) {
        this.#game = game
    }

    close() {
        this.#socket.send('CLOSE')
        this.#socket.close()
    }

    connect(host, port, loginCode, control) {
        let logged = false;

        const socket = new WebSocket(`ws://${host}:${port}`);
        this.#socket = socket

        const game = this.#game
        socket.onclose = function () {
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
            socket.send(control.getTickAction())
        };

    }

}
