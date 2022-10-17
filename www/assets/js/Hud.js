import * as Enum from "./Enums.js";

export class HUD {
    #game
    #cursor
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
    #scoreObject = null;
    #lastBuyMenuPlayerMoney = null;

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

    #renderRoundsHistory(history, halfTimeRoundNumber, meIsAttacker) {
        let template = ''
        if (halfTimeRoundNumber === null) {
            halfTimeRoundNumber = -1
        }

        for (let [roundNumber, data] of Object.entries(history)) {
            roundNumber = parseInt(roundNumber)
            let iWasAttacker = meIsAttacker
            if (roundNumber < halfTimeRoundNumber) {
                iWasAttacker = !meIsAttacker
            }

            if (roundNumber === halfTimeRoundNumber) {
                template += '<div class="round-number">|</div>'
            }
            const myTeamWonThatRound = (iWasAttacker === data.attackersWins)
            const direction = (myTeamWonThatRound ? 'round-win-my-team' : 'round-win-other-team')
            template += `<div class="round-number">
                <span class="${direction} round-win-${data.attackersWins ? '1' : '0'}">
                ${Enum.RoundEndReasonIcon[data.reason]}
                </span>
                <div>${roundNumber % 5 === 0 ? roundNumber : ''}</div>
            </div>`
        }
        return template
    }

    #renderPlayerStats(scoreBoardData, players, statsForOtherTeam) {
        const hud = this
        let playerTable = '';
        scoreBoardData.forEach(function (row) {
            const playerId = row.id
            const player = players[playerId].data
            playerTable += `
            <tr ${player.health > 0 ? '' : 'class="player-dead"'} data-player-id="${playerId}">
                <td>${hud.#getPlayerName(player, hud.#game.playerMe.data)}</td>
                <td data-money>${statsForOtherTeam ? '' : `${player.money}`}</td>
                <td data-kills>${row.kills}</td>
                <td data-deaths>${row.deaths}</td>
            </tr>
            `;
        })
        return `
        <table class="player-stats ${statsForOtherTeam ? 'team-other' : 'team-my'}">
            <thead>
            <tr>
                <th style="width:60%">Player name</th>
                <th style="width:20%">Money</th>
                <th style="width:10%">Kills</th>
                <th style="width:10%">Deaths</th>
            </tr>
            </thead>
            <tbody data-player-stats>
            ${playerTable}
            </tbody>
        </table>
        `;
    }

    requestFullScoreBoardUpdate(scoreObject) {
        this.#scoreObject = scoreObject
    }

    #updateScoreBoard() {
        if (this.#scoreObject === null) {
            return;
        }

        const game = this.#game
        const scoreObject = this.#scoreObject;
        const meIsAttacker = game.playerMe.isAttacker()
        const myTeamIndex = game.playerMe.getTeamIndex()
        const otherTeamIndex = game.playerMe.getOtherTeamIndex()

        this.#elements.scoreDetail.innerHTML = `
        <div>
            <table>
                <tr>
                    <td class="score-my color-me">${scoreObject.score[myTeamIndex]}<p>Score ${game.playerMe.getTeamName()}</p></td>
                    <td class="score-players players-my">
                        ${this.#renderPlayerStats(scoreObject.scoreboard[myTeamIndex], game.players, false)}
                    </td>
                </tr>
            </table>
        </div>
        <div>
            <table>
                <tr>
                    <td style="width:76px;text-align:center">
                        <small>Half Score</small>
                        <table class="half-score">
                            <td>
                                <span class="color-me">${scoreObject.firstHalfScore[scoreObject.halfTimeRoundNumber === null ? myTeamIndex : otherTeamIndex]}</span><br>
                                <small>1st</small><br>
                                <span class="color-opponent">${scoreObject.firstHalfScore[scoreObject.halfTimeRoundNumber === null ? otherTeamIndex : myTeamIndex]}</span>
                            </td>
                            ${scoreObject.halfTimeRoundNumber === null ? '' : `
                            <td>
                                <span class="color-me">${scoreObject.secondHalfScore[myTeamIndex]}</span><br>
                                <small>2nd</small><br>
                                <span class="color-opponent">${scoreObject.secondHalfScore[otherTeamIndex]}</span>
                            </td>`}
                        </table>
                    </td>
                    <td>
                        <div class="rounds-history">${this.#renderRoundsHistory(scoreObject.history, scoreObject.halfTimeRoundNumber, meIsAttacker)}</div>
                    </td>
                    <td style="width:135px">
                        <span class="color-me">$ ${scoreObject.lossBonus[myTeamIndex]}</span><br>
                        <small>Round loss Bonus</small><br>
                        <span class="color-opponent">$ ${scoreObject.lossBonus[otherTeamIndex]}</span>
                    </td>
                </tr>
            </table>
        </div>
        <div>
            <table>
                <tr>
                    <td class="score-opponent color-opponent">${scoreObject.score[otherTeamIndex]}<p>Score ${game.playerMe.getOtherTeamName()}</p></td>
                    <td class="score-players players-opponent">
                        ${this.#renderPlayerStats(scoreObject.scoreboard[otherTeamIndex], game.players, true)}
                    </td>
                </tr>
            </table>
        </div>
        `;
        this.#scoreObject = null
    }

    bombPlanted() {
        this.displayBottomMessage('<span class="text-danger">⚠️ Alert</span><br>The bomb has been planted.<br>40 seconds to detonation.')
    }

    #getPlayerName(player, playerMe) {
        if (player.id === playerMe.id) {
            return `Me (${Enum.ColorNames[player.color]})`
        }
        return (player.isAttacker === playerMe.isAttacker ? '' : 'Enemy ') + Enum.ColorNames[player.color]
    }

    #getPlayerStatRowElement(playerData) {
        return this.#elements.scoreDetail.querySelector(`[data-player-stats] [data-player-id="${playerData.id}"]`)
    }

    #updatePlayerKills(playerData, amount) {
        const killsElement = this.#getPlayerStatRowElement(playerData).querySelector('[data-kills]')
        let kills = parseInt(killsElement.innerText)
        killsElement.innerText = `${kills + amount}`
    }

    #updatePlayerAlive(playerData) {
        const statRowElement = this.#getPlayerStatRowElement(playerData)
        statRowElement.classList.add('player-dead')
        const deathsElement = statRowElement.querySelector('[data-deaths]')
        let deaths = parseInt(deathsElement.innerText)
        deathsElement.innerText = `${deaths - 1}`
    }

    updateMyTeamPlayerMoney(playerData, money) {
        const moneyElement = this.#getPlayerStatRowElement(playerData).querySelector('[data-money]')
        moneyElement.innerText = `${money}`
    }

    showKill(playerCulprit, playerDead, wasHeadshot, playerMe, killedItemId) {
        this.#updatePlayerAlive(playerDead)
        if (playerCulprit.id === playerDead.id) { // suicide
            this.#updatePlayerKills(playerDead, -1)
        } else if (playerCulprit.isAttacker === playerDead.isAttacker) { // team kill
            this.#updatePlayerKills(playerCulprit, -1)
        } else {
            this.#updatePlayerKills(playerCulprit, 1)
        }

        const culprit = document.createElement('span')
        const culpritOnMyTeam = (playerCulprit.isAttacker === playerMe.isAttacker)
        culprit.classList.add(culpritOnMyTeam ? 'team-me' : 'team-opponent')
        culprit.innerText = this.#getPlayerName(playerCulprit, playerMe)

        const dead = document.createElement('span')
        const deadOnyMyTeam = (playerDead.isAttacker === playerMe.isAttacker)
        dead.classList.add(deadOnyMyTeam ? 'team-me' : 'team-opponent')
        dead.innerText = this.#getPlayerName(playerDead, playerMe)

        const parentElement = this.#elements.killFeed
        if (parentElement.children.length > 4) {
            parentElement.children[0].remove()
        }

        const row = document.createElement('p')
        let shouldHighlight = (playerCulprit.id === playerMe.id || playerDead.id === playerMe.id)
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

    #refreshBuyMenu(playerData) {
        if (this.#lastBuyMenuPlayerMoney === playerData.money && this.#elements.buyMenu.innerHTML !== '') {
            return
        }

        const money = playerData.money
        const isAttacker = playerData.isAttacker
        const buyMenuElement = this.#elements.buyMenu
        this.#lastBuyMenuPlayerMoney = money

        buyMenuElement.innerHTML = `
            <p class="title">${this.#game.playerMe.getTeamName()} Buy Store. Your money balance $ <strong>${money}</strong></p>
            <h3>Equipment</h3>
            <p${money < 1000 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="10" class="hud-action action-buy">Buy Kevlar + Helmet for $ 1,000</a></p>
            <h3>Pistols</h3>
        ${isAttacker
            ? `<p${money < 200 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="11" class="hud-action action-buy">Buy Glock for $ 200</a></p>`
            : `<p${money < 200 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="8" class="hud-action action-buy">Buy USP for $ 200</a></p>`
        }
            <p${money < 250 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="9" class="hud-action action-buy">Buy P-250 for $ 250</a></p>
            <h3>Rifles</h3>
        ${isAttacker
            ? `<p${money < 2700 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="1" class="hud-action action-buy">Buy AK-47 for $ 2,700</a></p>`
            : `<p${money < 3100 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="7" class="hud-action action-buy">Buy M4-A1 for $ 3,100</a></p>`
        }
        `;
    }

    showShot() {
        clearTimeout(this.#shotAnimationInterval)
        this.#elements.shotModel.classList.remove('hidden');
        this.#shotAnimationInterval = setTimeout(() => this.#elements.shotModel.classList.add('hidden'), 30)
    }

    updateHud(player) {
        this.#updateScoreBoard()
        const hs = this.#setting
        if (hs.showScore) {
            this.#elements.score.classList.remove('hidden');
        } else {
            this.#elements.score.classList.add('hidden');
        }
        this.#elements.canBuyIcon.classList.toggle('hidden', !player.canBuy);
        if (player.canBuy && hs.showBuyMenu) {
            this.#cursor.requestUnLock()
            this.#refreshBuyMenu(player)
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

        let myTeamIndex = +player.isAttacker
        let otherTeamIndex = +!player.isAttacker
        this.#elements.scoreMyTeam.innerHTML = this.#game.score.score[myTeamIndex]
        this.#elements.scoreOpponentTeam.innerHTML = this.#game.score.score[otherTeamIndex]
        this.#elements.aliveMyTeam.innerHTML = this.#game.alivePlayers[myTeamIndex]
        this.#elements.aliveOpponentTeam.innerHTML = this.#game.alivePlayers[otherTeamIndex]
    }

    createHud(elementHud) {
        if (this.#elements.score) {
            throw new Error("HUD already created")
        }

        elementHud.innerHTML = `
        <div id="cross">✛</div>
        <div id="equipped-item">
            <div style="position:relative">
                <img data-shot class="hidden" src="./resources/shot.gif">
                <img data-slot src="./resources/slot_2.png">
            </div>
        </div>
        <div id="scoreboard" class="hidden">
            <div id="scoreboard-detail"></div>
        </div>
        <div id="buy-menu" class="hidden"></div>
        <section>
            <div class="left">
                <div class="top">
                    <div class="radar">
                        <br><br><br>
                        <br><br><br>
                        <br><br><br>
                    </div>
                    <div class="money bg"><span data-money>0</span> $ <span data-can-buy>🛒</span></div>
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
                    <p class="hidden" data-slot="1">Primary [1]</p>
                    <p class="highlight" data-slot="2">Secondary [2]</p>
                    <p class="hidden" data-slot="3">Bomb [5]</p>
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
        this.#elements.buyMenu.addEventListener('click', function (e) {
            if (!e.target.classList.contains('action-buy')) {
                return
            }

            game.buyList.push(e.target.dataset.buyMenuItemId)
        }, {capture: true})
    }
}
