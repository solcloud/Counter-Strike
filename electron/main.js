const {app, BrowserWindow, protocol} = require('electron')
const url = require('url')

app.whenReady().then(() => {

    protocol.interceptFileProtocol('file', function (request, callback) { // todo migrate www/ files to relative links and remove this
        const filePath = url.fileURLToPath('file://' + __dirname + '/../www' + request.url.slice('file://'.length))
        callback(filePath)
    })

    const win = new BrowserWindow({
        autoHideMenuBar: true,
        width: 800,
        height: 600,
        webPreferences: { // todo do it properly with isolation
            nodeIntegration: true,
            contextIsolation: false
        }
    })

    win.loadFile('/index.html')
})
