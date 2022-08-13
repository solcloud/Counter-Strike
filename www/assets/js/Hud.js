import * as Enum from "./Enums.js";

export class HUD {
    #game
    #setting = {
        showScore: false
    }
    #messages = {
        top: '',
        bottom: ''
    }
    #elements = {
        score: null,
        equippedItem: null,
        slotModel: null,
        inventory: null,
        money: null,
        health: null,
        armor: null,
        messageTop: null,
        messageBottom: null,
        scoreMyTeam: null,
        scoreOpponentTeam: null,
        aliveMyTeam: null,
        aliveOpponentTeam: null,
        time: null,
        killFeed: null,
    }
    #countDownIntervalId = null;

    setGame(game) {
        this.#game = game
    }

    pause(msg, timeMs) {
        this.#startCountDown(timeMs)
        this.displayTopMessage(msg)
    }

    showScore() {
        this.#setting.showScore = true
    }

    hideScore() {
        this.#setting.showScore = false
    }

    toggleScore() {
        this.#setting.showScore = !this.#setting.showScore
    }

    bombPlanted() {
        this.#messages.bottom = '<span class="text-danger">⚠️ Alert</span><br>The bomb has been planted.<br>40 seconds to detonation.'
    }

    showKill(playerCulprit, playerDead, wasHeadshot, playerDataMe, killedItemId) {
        let shouldHighlight = false
        const culprit = document.createElement('span')
        const culpritOnMyTeam = (playerCulprit.isAttacker === playerDataMe.isAttacker)
        culprit.classList.add(culpritOnMyTeam ? 'team-me' : 'team-opponent')
        if (playerCulprit.id === playerDataMe.id) {
            shouldHighlight = true
            culprit.innerText = `Me (${Enum.ColorNames[playerCulprit.color]})`
        } else {
            culprit.innerText = (culpritOnMyTeam ? '' : 'Enemy ') + Enum.ColorNames[playerCulprit.color]
        }

        const dead = document.createElement('span')
        const deadOnyMyTeam = (playerDead.isAttacker === playerDataMe.isAttacker)
        dead.classList.add(deadOnyMyTeam ? 'team-me' : 'team-opponent')
        if (playerDead.id === playerDataMe.id) {
            shouldHighlight = true
            dead.innerText = `Me (${Enum.ColorNames[playerDead.color]})`
        } else {
            dead.innerText = (deadOnyMyTeam ? '' : 'Enemy ') + Enum.ColorNames[playerDead.color]
        }

        const parentElement = this.#elements.killFeed
        if (parentElement.children.length > 4) {
            parentElement.children[0].remove()
        }

        const row = document.createElement('p')
        if (shouldHighlight) {
            row.classList.add('highlight')
        }
        let headshot = (wasHeadshot ? ' ⌖' : '')
        row.append(culprit)
        row.append(` killed${headshot} `)
        row.append(dead)
        parentElement.append(row)

        setTimeout(function () {
            row.remove()
        }, 3000)
    }

    roundStart(roundTimeMs) {
        this.#startCountDown(roundTimeMs)
    }

    #startCountDown(timeMs) {
        clearInterval(this.#countDownIntervalId)
        let roundTimeSec = Math.floor(timeMs / 1000)

        const timeElement = this.#elements.time
        let roundTimeInterval = setInterval(function () {
            roundTimeSec--

            let roundTimeMinute = Math.floor(roundTimeSec / 60)
            timeElement.innerText = `${roundTimeMinute.toString().padStart(2, '0')}:${(roundTimeSec % 60).toString().padStart(2, '0')}`
            if (roundTimeSec === 0) {
                clearInterval(roundTimeInterval)
            }
        }, 1000)
        this.#countDownIntervalId = roundTimeInterval
    }

    startWarmup(timeMs) {
        this.displayTopMessage('Warmup')
        this.#startCountDown(timeMs)
    }

    displayTopMessage(msg) {
        this.#messages.top = msg
    }

    clearAlerts() {
        this.clearTopMessage()
        this.clearBottomMessage()
        this.#elements.killFeed.innerHTML = ''
    }

    clearTopMessage() {
        this.#messages.top = ''
    }

    clearBottomMessage() {
        this.#messages.bottom = ''
    }

    equip(slotId) {
        this.#elements.slotModel.src = `/resources/slot_${slotId}.png`
        this.#elements.inventory.querySelectorAll('[data-slot]').forEach(function (node) {
            node.classList.remove('highlight')
        })
        this.#elements.inventory.querySelector(`[data-slot="${slotId}"]`).classList.add('highlight')
    }

    updateHud(player) {
        const hs = this.#setting
        if (hs.showScore) {
            this.#elements.score.classList.remove('hidden');
        } else {
            this.#elements.score.classList.add('hidden');
        }

        this.#elements.money.innerText = player.money
        this.#elements.health.innerText = player.health
        this.#elements.armor.innerText = player.armor
        this.#elements.messageTop.innerText = this.#messages.top
        this.#elements.messageBottom.innerHTML = this.#messages.bottom

        let myTeam = player.playingOnAttackers ? 'attackers' : 'defenders'
        let otherTeam = player.playingOnAttackers ? 'defenders' : 'attackers'
        this.#elements.scoreMyTeam.innerHTML = this.#game.score[myTeam]
        this.#elements.scoreOpponentTeam.innerHTML = this.#game.score[otherTeam]
        this.#elements.aliveMyTeam.innerHTML = this.#game.alivePlayers[myTeam]
        this.#elements.aliveOpponentTeam.innerHTML = this.#game.alivePlayers[otherTeam]
    }

    createHud(elementHud) {
        if (this.#elements.score) {
            throw new Error("HUD already created")
        }

        elementHud.innerHTML = `
        <div id="cross">✛</div>
        <div id="equipped-item"><img src="/resources/slot_2.png"></div>
        <div id="scoreboard" class="hidden">
            scoreboard: (kill, assist, death, hs %, money, name, MVP, score, K/D, ADDR, UD, EF)
        </div>
        <div id="buy-menu" class="hidden">
             BuyMenu weapon stats (ammo, kill award ($), damage, fire rate, recoil control, accurate range, armor penetration) and each hit box damage
        </div>
        <section>
            <div class="left">
                <div class="top">
                    <div class="radar">
                        <br><br><br>
                        <br><br><br>
                        <br><br><br>
                    </div>
                    <div class="money bg"><span data-money>0</span> $ 🛒</div>
                </div>
                <div class="bottom">
                    <div class="health row bg">
                        <div class="hp">
                            ➕ <span data-health>100</span>
                        </div>
                        <div class="hp">
                            🛡️ <span data-armor>0</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="middle">
                <div class="timer">
                    <div class="team-me-alive"></div>
                    <div class="timer-center bg">
                        <div id="time">---</div>
                        <div class="row">
                            <div class="team-me-score">0</div>
                            <div class="team-opponent-score">0</div>
                        </div>
                    </div>
                    <div class="team-opponent-alive"></div>
                </div>
                <div id="message-top"></div>
                <div id="message-bottom"></div>
            </div>
            <div class="right">
                <div class="kill-feed">
                </div>
                <div class="inventory">
                    <p data-slot="0">Knife  [q]</p>
                    <p data-slot="1">Rifle  [1]</p>
                    <p class="highlight" data-slot="2">Pistol [2]</p>
                </div>
                <div>
                    <span class="ammo bg">
                    12 / 24
                    </span>
                </div>
            </div>
        </section>
    `;

        this.#elements.score = elementHud.querySelector('#scoreboard')
        this.#elements.equippedItem = elementHud.querySelector('#equipped-item')
        this.#elements.slotModel = elementHud.querySelector('#equipped-item img')
        this.#elements.inventory = elementHud.querySelector('.inventory')
        this.#elements.money = elementHud.querySelector('[data-money]')
        this.#elements.health = elementHud.querySelector('[data-health]')
        this.#elements.armor = elementHud.querySelector('[data-armor]')
        this.#elements.messageTop = elementHud.querySelector('#message-top')
        this.#elements.messageBottom = elementHud.querySelector('#message-bottom')
        this.#elements.scoreMyTeam = elementHud.querySelector('.team-me-score')
        this.#elements.scoreOpponentTeam = elementHud.querySelector('.team-opponent-score')
        this.#elements.aliveMyTeam = elementHud.querySelector('.team-me-alive')
        this.#elements.aliveOpponentTeam = elementHud.querySelector('.team-opponent-alive')
        this.#elements.time = elementHud.querySelector('#time')
        this.#elements.killFeed = elementHud.querySelector('.kill-feed')
    }
}
