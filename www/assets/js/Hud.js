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
        scoreDetail: null,
        equippedItem: null,
        slotModel: null,
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
            const player = players[row['id']].data
            playerTable += `
            <tr ${player.health > 0 ? '' : 'class="player-dead"'}>
                <td>${hud.#getPlayerName(player, hud.#game.playerMe.data)}</td>
                <td>${statsForOtherTeam ? '' : `${player.money}`}</td>
                <td>${row.kills}</td>
                <td>${row.deaths}</td>
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
            <tbody>
            ${playerTable}
            </tbody>
        </table>
        `;
    }

    updateRoundsHistory(scoreObject) {
        const game = this.#game
        const meIsAttacker = game.playerMe.isAttacker()
        const myTeam = (meIsAttacker ? 'Attackers' : 'Defenders')
        const opponentTeam = (meIsAttacker ? 'Defenders' : 'Attackers')
        const myTeamIndex = game.playerMe.getTeamIndex()
        const otherTeamIndex = game.playerMe.getOtherTeamIndex()

        this.#elements.scoreDetail.innerHTML = `
        <div>
            <table>
                <tr>
                    <td class="score-my color-me">${scoreObject.score[myTeamIndex]}<p>Score ${myTeam}</p></td>
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
                    <td class="score-opponent color-opponent">${scoreObject.score[otherTeamIndex]}<p>Score ${opponentTeam}</p></td>
                    <td class="score-players players-opponent">
                        ${this.#renderPlayerStats(scoreObject.scoreboard[otherTeamIndex], game.players, true)}
                    </td>
                </tr>
            </table>
        </div>
    `;
    }

    bombPlanted() {
        this.#messages.bottom = '<span class="text-danger">⚠️ Alert</span><br>The bomb has been planted.<br>40 seconds to detonation.'
    }

    #getPlayerName(player, playerMe) {
        if (player.id === playerMe.id) {
            return `Me (${Enum.ColorNames[player.color]})`
        }
        return (player.isAttacker === playerMe.isAttacker ? '' : 'Enemy ') + Enum.ColorNames[player.color]
    }

    showKill(playerCulprit, playerDead, wasHeadshot, playerMe, killedItemId) {
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

    equip(slotId, availableSlots) {
        this.#elements.slotModel.src = `/resources/slot_${slotId}.png`
        this.#elements.inventory.querySelectorAll('[data-slot]').forEach(function (node) {
            node.classList.remove('highlight', 'hidden')
            if (!availableSlots[node.dataset.slot]) {
                node.classList.add('hidden')
            }
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
        if (player.ammo === null) {
            this.#elements.ammo.innerText = `${player.item.name}`
        } else {
            this.#elements.ammo.innerText = `${player.item.name} - ${player.ammo} / ${player.ammoReserve}`
        }
        this.#elements.messageTop.innerText = this.#messages.top
        this.#elements.messageBottom.innerHTML = this.#messages.bottom

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
        <div id="equipped-item"><img src="/resources/slot_2.png"></div>
        <div id="scoreboard" class="hidden">
            <div id="scoreboard-detail"></div>
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
        this.#elements.scoreDetail = elementHud.querySelector('#scoreboard-detail')
        this.#elements.equippedItem = elementHud.querySelector('#equipped-item')
        this.#elements.slotModel = elementHud.querySelector('#equipped-item img')
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
    }
}
