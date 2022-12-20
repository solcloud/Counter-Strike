import * as Enum from "./Enums.js";
import {BuyMenu} from "./hud/BuyMenu.js";
import {ScoreBoard} from "./hud/ScoreBoard.js";
import {KillFeed} from "./hud/KillFeed.js";
import {Radar} from "./hud/Radar.js";
import {HitFeedback} from "./hud/HitFeedback.js";
import {GameMenu} from "./hud/GameMenu.js";

export class HUD {
    #game
    #buyMenu = null;
    #scoreBoard = null;
    #gameMenu = null;
    #killFeed = null;
    #hitFeedback = null;
    #radar = null;
    #showAble = {
        showScore: false,
        showBuyMenu: false,
        showGameMenu: false,
    }
    #elements = {
        score: null,
        scoreDetail: null,
        buyMenu: null,
        gameMenu: null,
        canBuyIcon: null,
        canPlantIcon: null,
        haveDefuseKit: null,
        spectateUi: null,
        equippedItem: null,
        shotModel: null,
        dropModel: null,
        inventory: null,
        money: null,
        health: null,
        armor: null,
        armorType: null,
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

    injectDependency(game) {
        this.#game = game
    }

    pause(msg, timeMs) {
        this.#startCountDown(timeMs)
        this.displayTopMessage(msg)
    }

    toggleBuyMenu() {
        this.#showAble.showBuyMenu = !this.#showAble.showBuyMenu
    }

    toggleGameMenu() {
        this.#showAble.showGameMenu = !this.#showAble.showGameMenu
    }

    toggleScore(enabled) {
        this.#showAble.showScore = enabled
    }

    showScore() {
        this.#elements.score.classList.remove('hidden')
    }

    bombPlanted(detonationTimeSec) {
        this.#resetCountDown()
        this.#elements.time.innerText = '⚠️ 💣'
        this.displayBottomMessage(`<span class="text-danger">⚠️ Alert</span><br>The bomb has been planted.<br>${detonationTimeSec} seconds to detonation.`)
        setTimeout(() => this.clearBottomMessage(), 3000)
    }

    requestFullScoreBoardUpdate(scoreBoardData) {
        this.#scoreBoardData = scoreBoardData
    }

    updateMyTeamPlayerMoney(playerData, money) {
        const moneyElement = this.#scoreBoard.getPlayerStatRowElement(playerData).querySelector('[data-money]')
        moneyElement.innerText = `${money}`
    }

    spectatorHit(fromLeft, fromRight, fromFront, fromBack) {
        this.#hitFeedback.hit(fromLeft, fromRight, fromFront, fromBack)
    }

    showKill(playerCulprit, playerDead, wasHeadshot, playerMe, killedItemId) {
        this.#killFeed.showKill(playerCulprit, playerDead, wasHeadshot, playerMe, killedItemId)
    }

    roundStart(roundTimeMs) {
        this.#startCountDown(roundTimeMs)
    }

    #resetCountDown() {
        clearInterval(this.#countDownIntervalId)
    }

    #startCountDown(timeMs) {
        this.#resetCountDown()
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
        this.#elements.inventory.querySelectorAll('[data-slot]').forEach(function (node) {
            node.classList.remove('highlight', 'hidden')
            if (availableSlots[node.dataset.slot] === undefined) {
                node.classList.add('hidden')
            }
        })
        this.#elements.inventory.querySelector(`[data-slot="${slotId}"]`).classList.add('highlight')
    }

    showShot(item) {
        clearTimeout(this.#shotAnimationInterval)
        this.#elements.shotModel.classList.remove('hidden');
        this.#shotAnimationInterval = setTimeout(() => this.#elements.shotModel.classList.add('hidden'), 30)
    }

    showDropAnimation(item) {
        clearTimeout(this.#shotAnimationInterval)
        this.#elements.dropModel.classList.remove('hidden');
        this.#shotAnimationInterval = setTimeout(() => this.#elements.dropModel.classList.add('hidden'), 100)
    }

    updateHud(player) {
        if (this.#radar) {
            this.#radar.update(
                this.#game.getMyTeamPlayers(),
                this.#game.playerSpectate.getId(),
                this.#game.getPlayerSpectateRotation()[0],
                this.#game.playerMe.isAttacker() ? this.#game.bombDropPosition : null
            )
        }
        if (this.#scoreBoardData !== null) {
            this.#scoreBoard.update(this.#scoreBoardData)
            this.#scoreBoardData = null
        }
        this.#elements.score.classList.toggle('hidden', !this.#showAble.showScore);
        this.#elements.canBuyIcon.classList.toggle('hidden', !player.canBuy);
        this.#elements.canPlantIcon.classList.toggle('hidden', !player.canPlant);
        this.#elements.haveDefuseKit.classList.toggle('hidden', (player.slots[Enum.InventorySlot.SLOT_KIT] === undefined));
        this.#elements.spectateUi.classList.toggle('hidden', this.#game.playerMe.getId() === this.#game.playerSpectate.getId());
        if (player.canBuy && this.#showAble.showBuyMenu) {
            this.#game.requestPointerUnLock()
            this.#buyMenu.refresh(player, this.#game.playerMe.getTeamName())
            this.#elements.buyMenu.classList.remove('hidden');
        } else if (!this.#elements.buyMenu.classList.contains('hidden')) {
            this.#game.requestPointerLock()
            this.#elements.buyMenu.innerHTML = ''
            this.#elements.buyMenu.classList.add('hidden');
            this.#showAble.showBuyMenu = false
        }
        if (this.#showAble.showGameMenu) {
            this.#game.requestPointerUnLock()
            this.#gameMenu.show()
            this.#elements.gameMenu.classList.remove('hidden');
        } else if (!this.#elements.gameMenu.classList.contains('hidden')) {
            this.#game.requestPointerLock()
            this.#gameMenu.close()
            this.#elements.gameMenu.classList.add('hidden');
            this.#showAble.showGameMenu = false
        }

        this.#elements.money.innerText = player.money
        this.#elements.health.innerText = player.health
        this.#elements.armor.innerText = player.armor
        this.#elements.armorType.innerText = Enum.ArmorTypeIcon[player.armorType]
        if (player.ammo === null) {
            this.#elements.ammo.innerText = Enum.ItemIdToName[player.item.id]
        } else {
            this.#elements.ammo.innerText = `${Enum.ItemIdToName[player.item.id]} - ${player.ammo} / ${player.ammoReserve}`
        }

        let myTeamIndex = this.#game.playerMe.getTeamIndex()
        let otherTeamIndex = this.#game.playerMe.getOtherTeamIndex()
        this.#elements.scoreMyTeam.innerHTML = this.#game.score.score[myTeamIndex]
        this.#elements.scoreOpponentTeam.innerHTML = this.#game.score.score[otherTeamIndex]
        this.#elements.aliveMyTeam.innerHTML = this.#game.alivePlayers[myTeamIndex]
        this.#elements.aliveOpponentTeam.innerHTML = this.#game.alivePlayers[otherTeamIndex]
    }

    createHud(elementHud, map, setting) {
        if (this.#elements.score) {
            throw new Error("HUD already created")
        }

        elementHud.innerHTML = `
        <div id="cross"></div>
        <div id="hit-feedback"></div>
        <div id="equipped-item">
            <div>
                <img data-shot class="hidden" src="./resources/shot.gif">
                <img data-drop class="hidden" src="./resources/drop.gif">
            </div>
        </div>
        <div id="scoreboard" class="hidden">
            <div id="scoreboard-detail"></div>
        </div>
        <div id="buy-menu" class="hidden"></div>
        <div id="game-menu" class="hidden"></div>
        <section>
            <div class="left">
                <div class="top">
                    <div id="radar">
                        <canvas id="radar-canvas"></canvas>
                    </div>
                    <div id="mode-spectate" class="hidden" style="padding:12px 4px">Spectating</div>
                    <div class="money bg"><span data-money>0</span> $ <span data-can-buy>🛒</span></div>
                    <div data-can-plant class="hidden" style="margin:22px 4px">⇣&nbsp;💣&nbsp;⇣</div>
                    <div data-have-defuse-kit class="hidden" style="margin:22px 4px;font-size:140%">✂</div>
                </div>
                <div class="bottom">
                    <div id="fps-stats"></div>
                    <div class="health row bg">
                        <div class="hp">
                            ➕ <span data-health>100</span>
                        </div>
                        <div class="hp">
                            <span data-armor-type>♢️</span> <span data-armor>0</span>
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
                    <p data-slot="${Enum.InventorySlot.SLOT_KNIFE}">Knife</p>
                    <p class="hidden" data-slot="${Enum.InventorySlot.SLOT_PRIMARY}">Primary</p>
                    <p class="hidden" data-slot="${Enum.InventorySlot.SLOT_SECONDARY}">Secondary</p>
                    <p class="hidden" data-slot="${Enum.InventorySlot.SLOT_BOMB}">Bomb</p>
                </div>
                <div>
                    <span data-ammo class="ammo bg"></span>
                </div>
            </div>
        </section>
    `;

        this.#elements.score = elementHud.querySelector('#scoreboard')
        this.#elements.buyMenu = elementHud.querySelector('#buy-menu')
        this.#elements.gameMenu = elementHud.querySelector('#game-menu')
        this.#elements.canBuyIcon = elementHud.querySelector('[data-can-buy]')
        this.#elements.canPlantIcon = elementHud.querySelector('[data-can-plant]')
        this.#elements.haveDefuseKit = elementHud.querySelector('[data-have-defuse-kit]')
        this.#elements.spectateUi = elementHud.querySelector('#mode-spectate')
        this.#elements.scoreDetail = elementHud.querySelector('#scoreboard-detail')
        this.#elements.equippedItem = elementHud.querySelector('#equipped-item')
        this.#elements.shotModel = elementHud.querySelector('#equipped-item img[data-shot]')
        this.#elements.dropModel = elementHud.querySelector('#equipped-item img[data-drop]')
        this.#elements.inventory = elementHud.querySelector('.inventory')
        this.#elements.money = elementHud.querySelector('[data-money]')
        this.#elements.health = elementHud.querySelector('[data-health]')
        this.#elements.armor = elementHud.querySelector('[data-armor]')
        this.#elements.armorType = elementHud.querySelector('[data-armor-type]')
        this.#elements.ammo = elementHud.querySelector('[data-ammo]')
        this.#elements.messageTop = elementHud.querySelector('#message-top')
        this.#elements.messageBottom = elementHud.querySelector('#message-bottom')
        this.#elements.scoreMyTeam = elementHud.querySelector('.team-me-score')
        this.#elements.scoreOpponentTeam = elementHud.querySelector('.team-opponent-score')
        this.#elements.aliveMyTeam = elementHud.querySelector('.team-me-alive')
        this.#elements.aliveOpponentTeam = elementHud.querySelector('.team-opponent-alive')
        this.#elements.time = elementHud.querySelector('#time')
        this.#elements.killFeed = elementHud.querySelector('.kill-feed')

        const cross = elementHud.querySelector('#cross')
        cross.innerText = setting.getCrosshairSymbol()
        setting.addUpdateCallback('crosshairColor', (newValue) => cross.style.color = newValue)
        setting.update('crosshairColor', setting.getCrosshairColor())

        const game = this.#game
        this.#buyMenu = new BuyMenu(this.#elements.buyMenu)
        this.#scoreBoard = new ScoreBoard(game, this.#elements.scoreDetail)
        this.#gameMenu = new GameMenu(this.#elements.gameMenu, setting)
        this.#killFeed = new KillFeed(this.#scoreBoard, this.#elements.killFeed)
        this.#hitFeedback = new HitFeedback(elementHud.querySelector('#hit-feedback'))

        const self = this
        const radarImage = new Image()
        radarImage.onload = function () {
            const radarCanvas = elementHud.querySelector('#radar-canvas')
            radarCanvas.width = this.width
            radarCanvas.height = this.height
            self.#radar = new Radar(radarCanvas, radarImage, map, setting.getRadarZoom())
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
