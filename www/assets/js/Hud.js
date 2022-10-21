import * as Enum from "./Enums.js";
import {BuyMenu} from "./hud/BuyMenu.js";
import {ScoreBoard} from "./hud/ScoreBoard.js";
import {KillFeed} from "./hud/KillFeed.js";
import {Radar} from "./hud/Radar.js";

export class HUD {
    #game
    #cursor
    #buyMenu = null;
    #scoreBoard = null;
    #killFeed = null;
    #radar = null;
    #setting = {
        showScore: false,
        showBuyMenu: false
    }
    #elements = {
        score: null,
        scoreDetail: null,
        buyMenu: null,
        canBuyIcon: null,
        equippedItem: null,
        slotModel: null,
        shotModel: null,
        inventory: null,
        money: null,
        health: null,
        armor: null,
        ammo: null,
        messageTop: null,
        messageBottom: null,
        scoreMyTeam: null,
        scoreOpponentTeam: null,
        aliveMyTeam: null,
        aliveOpponentTeam: null,
        time: null,
        killFeed: null,
    }
    #shotAnimationInterval = null;
    #countDownIntervalId = null;
    #scoreBoardData = null;

    injectDependency(game, cursor) {
        this.#game = game
        this.#cursor = cursor
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

    toggleBuyMenu() {
        this.#setting.showBuyMenu = !this.#setting.showBuyMenu
    }

    hideBuyMenu() {
        this.#setting.showBuyMenu = false
    }

    toggleScore() {
        this.#setting.showScore = !this.#setting.showScore
    }

    bombPlanted() {
        this.displayBottomMessage('<span class="text-danger">⚠️ Alert</span><br>The bomb has been planted.<br>40 seconds to detonation.')
    }

    requestFullScoreBoardUpdate(scoreBoardData) {
        this.#scoreBoardData = scoreBoardData
    }

    updateMyTeamPlayerMoney(playerData, money) {
        const moneyElement = this.#scoreBoard.getPlayerStatRowElement(playerData).querySelector('[data-money]')
        moneyElement.innerText = `${money}`
    }

    showKill(playerCulprit, playerDead, wasHeadshot, playerMe, killedItemId) {
        this.#killFeed.showKill(playerCulprit, playerDead, wasHeadshot, playerMe, killedItemId)
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
        this.displayTopMessage('Waiting for all players to connect')
        this.#startCountDown(timeMs)
    }

    displayTopMessage(msg) {
        this.#elements.messageTop.innerText = msg
    }

    displayBottomMessage(msg) {
        this.#elements.messageBottom.innerHTML = msg
    }

    clearAlerts() {
        this.clearTopMessage()
        this.clearBottomMessage()
        this.#elements.killFeed.innerHTML = ''
    }

    clearTopMessage() {
        this.#elements.messageTop.innerText = ''
    }

    clearBottomMessage() {
        this.#elements.messageBottom.innerHTML = ''
    }

    equip(slotId, availableSlots) {
        this.#elements.slotModel.src = `./resources/slot_${slotId}.png`
        this.#elements.inventory.querySelectorAll('[data-slot]').forEach(function (node) {
            node.classList.remove('highlight', 'hidden')
            if (!availableSlots[node.dataset.slot]) {
                node.classList.add('hidden')
            }
        })
        this.#elements.inventory.querySelector(`[data-slot="${slotId}"]`).classList.add('highlight')
    }

    showShot() {
        clearTimeout(this.#shotAnimationInterval)
        this.#elements.shotModel.classList.remove('hidden');
        this.#shotAnimationInterval = setTimeout(() => this.#elements.shotModel.classList.add('hidden'), 30)
    }

    updateHud(player) {
        const hs = this.#setting
        if (this.#radar) {
            this.#radar.update(this.#game.getMyTeamPlayers(), this.#game.playerMe.getId(), this.#cursor.getRotation()[0])
        }
        if (this.#scoreBoardData !== null) {
            this.#scoreBoard.update(this.#scoreBoardData)
            this.#scoreBoardData = null
        }
        if (hs.showScore) {
            this.#elements.score.classList.remove('hidden');
        } else {
            this.#elements.score.classList.add('hidden');
        }
        this.#elements.canBuyIcon.classList.toggle('hidden', !player.canBuy);
        if (player.canBuy && hs.showBuyMenu) {
            this.#cursor.requestUnLock()
            this.#buyMenu.refresh(player, this.#game.playerMe.getTeamName())
            this.#elements.buyMenu.classList.remove('hidden');
        } else if (!this.#elements.buyMenu.classList.contains('hidden')) {
            this.#cursor.requestLock()
            this.#elements.buyMenu.innerHTML = ''
            this.#elements.buyMenu.classList.add('hidden');
        }

        this.#elements.money.innerText = player.money
        this.#elements.health.innerText = player.health
        this.#elements.armor.innerText = player.armor
        if (player.ammo === null) {
            this.#elements.ammo.innerText = `${player.item.name}`
        } else {
            this.#elements.ammo.innerText = `${player.item.name} - ${player.ammo} / ${player.ammoReserve}`
        }

        let myTeamIndex = this.#game.playerMe.getTeamIndex()
        let otherTeamIndex = this.#game.playerMe.getOtherTeamIndex()
        this.#elements.scoreMyTeam.innerHTML = this.#game.score.score[myTeamIndex]
        this.#elements.scoreOpponentTeam.innerHTML = this.#game.score.score[otherTeamIndex]
        this.#elements.aliveMyTeam.innerHTML = this.#game.alivePlayers[myTeamIndex]
        this.#elements.aliveOpponentTeam.innerHTML = this.#game.alivePlayers[otherTeamIndex]
    }

    createHud(elementHud, map) {
        if (this.#elements.score) {
            throw new Error("HUD already created")
        }

        elementHud.innerHTML = `
        <div id="cross">✛</div>
        <div id="equipped-item">
            <div style="position:relative">
                <img data-shot class="hidden" src="./resources/shot.gif">
                <img data-slot src="./resources/slot_${Enum.InventorySlot.SLOT_SECONDARY}.png">
            </div>
        </div>
        <div id="scoreboard" class="hidden">
            <div id="scoreboard-detail"></div>
        </div>
        <div id="buy-menu" class="hidden"></div>
        <section>
            <div class="left">
                <div class="top">
                    <div id="radar">
                        <canvas id="radar-canvas"></canvas>
                    </div>
                    <div class="money bg"><span data-money>0</span> $ <span data-can-buy>🛒</span></div>
                </div>
                <div class="bottom">
                    <div id="fps-stats"></div>
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
                    <p data-slot="${Enum.InventorySlot.SLOT_KNIFE}">Knife [q]</p>
                    <p class="hidden" data-slot="${Enum.InventorySlot.SLOT_PRIMARY}">Primary [1]</p>
                    <p class="highlight" data-slot="${Enum.InventorySlot.SLOT_SECONDARY}">Secondary [2]</p>
                    <p class="hidden" data-slot="${Enum.InventorySlot.SLOT_BOMB}">Bomb [5]</p>
                </div>
                <div>
                    <span data-ammo class="ammo bg">
                    </span>
                </div>
            </div>
        </section>
    `;

        this.#elements.score = elementHud.querySelector('#scoreboard')
        this.#elements.buyMenu = elementHud.querySelector('#buy-menu')
        this.#elements.canBuyIcon = elementHud.querySelector('[data-can-buy]')
        this.#elements.scoreDetail = elementHud.querySelector('#scoreboard-detail')
        this.#elements.equippedItem = elementHud.querySelector('#equipped-item')
        this.#elements.slotModel = elementHud.querySelector('#equipped-item img[data-slot]')
        this.#elements.shotModel = elementHud.querySelector('#equipped-item img[data-shot]')
        this.#elements.inventory = elementHud.querySelector('.inventory')
        this.#elements.money = elementHud.querySelector('[data-money]')
        this.#elements.health = elementHud.querySelector('[data-health]')
        this.#elements.armor = elementHud.querySelector('[data-armor]')
        this.#elements.ammo = elementHud.querySelector('[data-ammo]')
        this.#elements.messageTop = elementHud.querySelector('#message-top')
        this.#elements.messageBottom = elementHud.querySelector('#message-bottom')
        this.#elements.scoreMyTeam = elementHud.querySelector('.team-me-score')
        this.#elements.scoreOpponentTeam = elementHud.querySelector('.team-opponent-score')
        this.#elements.aliveMyTeam = elementHud.querySelector('.team-me-alive')
        this.#elements.aliveOpponentTeam = elementHud.querySelector('.team-opponent-alive')
        this.#elements.time = elementHud.querySelector('#time')
        this.#elements.killFeed = elementHud.querySelector('.kill-feed')

        const game = this.#game
        this.#buyMenu = new BuyMenu(this.#elements.buyMenu)
        this.#scoreBoard = new ScoreBoard(game, this.#elements.scoreDetail)
        this.#killFeed = new KillFeed(this.#scoreBoard, this.#elements.killFeed)

        const self = this
        const radarImage = new Image()
        radarImage.onload = function () {
            const radarCanvas = elementHud.querySelector('#radar-canvas')
            radarCanvas.width = this.width
            radarCanvas.height = this.height
            self.#radar = new Radar(radarCanvas, radarImage, map)
        }
        radarImage.src = `./resources/map/${map}.png`

        this.#elements.buyMenu.addEventListener('click', function (e) {
            if (!e.target.classList.contains('action-buy')) {
                return
            }

            game.buyList.push(e.target.dataset.buyMenuItemId)
        }, {capture: true})
    }
}
