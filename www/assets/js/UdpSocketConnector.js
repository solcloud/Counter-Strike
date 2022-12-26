const dgram = window.nodeApi.dgram

export class UdpSocketConnector {
    #game;
    #socket;

    constructor(game) {
        this.#game = game
    }

    close() {
        this.#socket.close()
    }

    connect(host, port, loginCode, control) {
        let logged = false;

        const socket = dgram.createSocket('udp4');
        this.#socket = socket

        const game = this.#game
        socket.on('close', function () {
            console.log("UdpSocket closed")
        });
        socket.on('error', function (error) {
            alert(`Cannot connect to '${host}:${port}'`)
            console.log("UdpSocket error: " + error.message)
        });
        socket.on('connect', function () {
            console.log("UdpSocket connection established.")
            if (!logged) {
                console.log("Sending login code to server.")
                socket.send("login " + loginCode)
                logged = true
            }
        });
        socket.on('message', function (msg) {
            let state
            try {
                state = JSON.parse(msg.toString())
            } catch (err) {
                game.end("Message parse error! " + err.message)
                return
            }

            game.tick(state)
            const response = control.getTickAction()
            if (response !== '') {
                socket.send(response)
            }
        });

        socket.connect(port, host)
    }

}
