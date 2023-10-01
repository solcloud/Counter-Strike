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
        flash: null,
        score: null,
        scoreDetail: null,
        buyMenu: null,
        gameMenu: null,
        canBuyIcon: null,
        canPlantIcon: null,
        haveDefuseKit: null,
        haveBomb: null,
        spectateUi: null,
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
        cross: null,
        scope: null,
    }
    #flashInterval = null;
    #countDownIntervalId = null;
    #scoreBoardData = null;
    #weaponSlots = [Enum.InventorySlot.SLOT_PRIMARY, Enum.InventorySlot.SLOT_SECONDARY, Enum.InventorySlot.SLOT_KNIFE]
    #grenadeSlots = [Enum.InventorySlot.SLOT_GRENADE_DECOY, Enum.InventorySlot.SLOT_GRENADE_HE, Enum.InventorySlot.SLOT_GRENADE_MOLOTOV, Enum.InventorySlot.SLOT_GRENADE_SMOKE, Enum.InventorySlot.SLOT_GRENADE_FLASH]

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
        this.displayBottomMessage(`<span class="text-danger">⚠️ Alert</span><br>The bomb has been planted.<br>${detonationTimeSec} seconds till detonation.`)
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

    showFlashBangScreen(fullFlashTimeMs = 1000, resetTimeMs = 3000) {
        clearTimeout(this.#flashInterval)
        const element = this.#elements.flash
        element.style.opacity = 1.0
        const timePortion = resetTimeMs / 100

        const callback = function () {
            element.style.opacity -= 0.01
            if (element.style.opacity < .01) {
                element.style.opacity = 0
            } else {
                setTimeout(callback, timePortion)
            }
        }
        this.#flashInterval = setTimeout(callback, fullFlashTimeMs)
    }

    updateCrossHair(scopeLevel) {
        this.#elements.cross.style.opacity = (scopeLevel === 0 ? 1.0 : 0.0)
        this.#elements.scope.style.opacity = (scopeLevel === 0 ? 0.0 : 1.0)
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

    changeSpectatePlayer(player) {
        this.#elements.spectateUi.querySelector('span').innerText = Enum.ColorNames[player.getColorIndex()]
    }

    equip(slotId, itemId, availableSlots) {
        let html = ''
        this.#weaponSlots.forEach((slot) => {
            const item = availableSlots[slot]
            if (!item) {
                return
            }

            html += `<p${slotId === slot ? ' class="highlight"' : ''}>${Enum.ItemIdToIcon[item.id]}</p>`
        })
        let grenade = ''
        this.#grenadeSlots.forEach((slot) => {
            const item = availableSlots[slot]
            if (!item) {
                return
            }

            grenade += `<span${slotId === slot ? ' class="highlight"' : ''}>${Enum.ItemIdToIcon[item.id]}</span>`
        })
        this.#elements.inventory.innerHTML = html + `<div class="grenades">${grenade}</div>`
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
        this.#elements.haveBomb.classList.toggle('hidden', (player.slots[Enum.InventorySlot.SLOT_BOMB] === undefined));
        this.#elements.spectateUi.classList.toggle('hidden', this.#game.playerMe.getId() === this.#game.playerSpectate.getId());
        if (player.canBuy && this.#showAble.showBuyMenu) {
            if (this.#elements.buyMenu.classList.contains('hidden')) {
                this.#elements.buyMenu.classList.remove('hidden')
                this.#game.requestPointerUnLock()
            }
            this.#buyMenu.refresh(player, this.#game.playerMe.getTeamName())
        } else if (!this.#elements.buyMenu.classList.contains('hidden')) {
            this.#elements.buyMenu.classList.add('hidden')
            this.#showAble.showBuyMenu = false
            this.#game.requestPointerLock()
        } else {
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
            this.#elements.ammo.innerText = `${Enum.ItemIdToName[player.item.id]} \u00a0 ${player.ammo} / ${player.ammoReserve}`
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
        <div id="scope"><div class="scope-cross"></div></div>
        <div id="flash"></div>
    <div id="hud-container">
        <div id="cross"></div>
        <div id="hit-feedback"></div>
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
                    <div class="money bg"><span data-money>0</span> $ <span data-can-buy>🛒</span></div>
                    <div data-have-bomb class="hidden icons" style="margin:22px 4px;font-size:200%;color:#fdf46e"><span data-can-plant class="hidden">🠻&nbsp;</span>\uE031</div>
                    <div data-have-defuse-kit class="hidden icons" style="margin:22px 4px;font-size:200%;color:#5d8dea">\uE066</div>
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
                <div id="mode-spectate" class="hidden">Spectating player <span></span></div>
            </div>
            <div class="right">
                <div class="kill-feed icons">
                </div>
                <div class="inventory icons"></div>
                <div>
                    <span data-ammo class="ammo bg"></span>
                </div>
            </div>
        </section>
    </div>
    `;

        elementHud.style.setProperty('--flash-bang-color', setting.getFlashBangColor())
        elementHud.style.setProperty('--scope-size', setting.getScopeSize())
        elementHud.style.setProperty('--hud-color', setting.getHudColor())
        elementHud.style.setProperty('--hud-color-shadow', setting.getHudColorShadow())

        this.#elements.flash = elementHud.querySelector('#flash')
        this.#elements.score = elementHud.querySelector('#scoreboard')
        this.#elements.buyMenu = elementHud.querySelector('#buy-menu')
        this.#elements.gameMenu = elementHud.querySelector('#game-menu')
        this.#elements.canBuyIcon = elementHud.querySelector('[data-can-buy]')
        this.#elements.canPlantIcon = elementHud.querySelector('[data-can-plant]')
        this.#elements.haveDefuseKit = elementHud.querySelector('[data-have-defuse-kit]')
        this.#elements.haveBomb = elementHud.querySelector('[data-have-bomb]')
        this.#elements.spectateUi = elementHud.querySelector('#mode-spectate')
        this.#elements.scoreDetail = elementHud.querySelector('#scoreboard-detail')
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
        this.#elements.cross = elementHud.querySelector('#cross')
        this.#elements.scope = elementHud.querySelector('#scope')

        this.#elements.cross.innerText = setting.getCrosshairSymbol()
        setting.addUpdateCallback('crosshairColor', (newValue) => cross.style.color = newValue)
        setting.update('crosshairColor', setting.getCrosshairColor())
        setting.addUpdateCallback('crosshairSize', (newValue) => cross.style.fontSize = newValue + 'px')
        setting.update('crosshairSize', setting.getCrosshairSize())

        const game = this.#game
        this.#buyMenu = new BuyMenu(this.#elements.buyMenu)
        this.#scoreBoard = new ScoreBoard(game, this.#elements.scoreDetail)
        this.#gameMenu = new GameMenu(this.#elements.gameMenu, setting, this)
        this.#killFeed = new KillFeed(this.#scoreBoard, this.#elements.killFeed)
        this.#hitFeedback = new HitFeedback(elementHud.querySelector('#hit-feedback'))

        const self = this
        const radarImage = new Image()
        radarImage.onload = function () {
            const radarCanvas = elementHud.querySelector('#radar-canvas')
            radarCanvas.width = this.width
            radarCanvas.height = this.height
            self.#radar = new Radar(radarCanvas, radarImage, map, setting.getRadarZoom())
            setting.addUpdateCallback('radarZoom', (newValue) => self.#radar.setZoom(newValue))
        }
        radarImage.src = `./resources/map/${map}.png`

        this.#elements.buyMenu.addEventListener('click', function (e) {
            if (!e.target.classList.contains('action-buy')) {
                return
            }

            e.stopPropagation()
            game.buyList.push(e.target.dataset.buyMenuItemId)
        }, {capture: true})
    }
}
