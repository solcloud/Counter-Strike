const {app, BrowserWindow} = require('electron')
const path = require('path')

app.whenReady().then(() => {
    const win = new BrowserWindow({
        autoHideMenuBar: true,
        width: 800,
        height: 600,
        webPreferences: {
            devTools: false,
            nodeIntegration: false,
            sandbox: false,
            contextIsolation: false,
            preload: path.join(__dirname, 'preload.js')
        }
    })

    win.loadFile('../www/index.html')
})
