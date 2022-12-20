import * as Enum from "../Enums.js";

export class ScoreBoard {
    #element
    #game

    constructor(game, scoreBoardElement) {
        this.#game = game
        this.#element = scoreBoardElement
    }

    #renderPlayerStats(scoreBoardData, players, roundNumber, statsForOtherTeam) {
        const self = this
        let playerTable = '';
        scoreBoardData.forEach(function (row) {
            const playerId = row.id
            const player = players[playerId].data
            playerTable += `
            <tr ${player.health > 0 ? '' : 'class="player-dead"'} data-player-id="${playerId}">
                <td>${self.getPlayerName(player, self.#game.playerMe.data)}</td>
                <td data-money>${statsForOtherTeam ? '' : `${player.money}`}</td>
                <td data-kills>${row.kills}</td>
                <td data-deaths>${row.deaths}</td>
                <td data-adr>${Math.round(row.damage / Math.max(1, roundNumber - 1))}</td>
            </tr>
            `;
        })

        return `
            <table class="player-stats ${statsForOtherTeam ? 'team-other' : 'team-my'}">
                <thead>
                <tr>
                    <th style="width:50%">Player name</th>
                    <th style="width:20%">Money</th>
                    <th style="width:10%">Kills</th>
                    <th style="width:10%">Deaths</th>
                    <th style="width:10%">ADR</th>
                </tr>
                </thead>
                <tbody data-player-stats>
                ${playerTable}
                </tbody>
            </table>
        `;
    }

    #renderRoundsHistory(history, halfTimeRoundNumber, meIsAttacker) {
        let template = ''
        if (halfTimeRoundNumber === null) {
            halfTimeRoundNumber = -1
        }

        for (let [roundNumber, data] of Object.entries(history)) {
            roundNumber = parseInt(roundNumber)
            let iWasAttacker = meIsAttacker
            if (roundNumber <= halfTimeRoundNumber) {
                iWasAttacker = !meIsAttacker
            }

            const myTeamWonThatRound = (iWasAttacker === data.attackersWins)
            const direction = (myTeamWonThatRound ? 'round-win-my-team' : 'round-win-other-team')
            template += `<div class="round-number">
                <span class="${direction} round-win-${data.attackersWins ? '1' : '0'}">
                ${Enum.RoundEndReasonIcon[data.reason]}
                </span>
                <div>${roundNumber % 5 === 0 ? roundNumber : ''}</div>
            </div>`

            if (roundNumber === halfTimeRoundNumber) {
                template += '<div class="round-number">|</div>'
            }
        }

        return template
    }

    getPlayerName(player, playerMe) {
        if (player.id === playerMe.id) {
            return `Me (${Enum.ColorNames[player.color]})`
        }
        return (player.isAttacker === playerMe.isAttacker ? '' : 'Enemy ') + Enum.ColorNames[player.color]
    }

    getPlayerStatRowElement(playerData) {
        return this.#element.querySelector(`[data-player-stats] [data-player-id="${playerData.id}"]`)
    }

    updatePlayerKills(playerData, amount) {
        const killsElement = this.getPlayerStatRowElement(playerData).querySelector('[data-kills]')
        let kills = parseInt(killsElement.innerText)
        killsElement.innerText = `${kills + amount}`
    }

    updatePlayerIsDead(playerData) {
        const statRowElement = this.getPlayerStatRowElement(playerData)
        statRowElement.classList.add('player-dead')
        const deathsElement = statRowElement.querySelector('[data-deaths]')
        let deaths = parseInt(deathsElement.innerText)
        deathsElement.innerText = `${deaths + 1}`
    }

    update(scoreData) {
        const game = this.#game
        const roundNumber = game.getRoundNumber()
        const meIsAttacker = game.playerMe.isAttacker()
        const myTeamIndex = game.playerMe.getTeamIndex()
        const otherTeamIndex = game.playerMe.getOtherTeamIndex()

        this.#element.innerHTML = `
        <div>
            <table>
                <tr>
                    <td class="score-my color-me">${scoreData.score[myTeamIndex]}<p>Score ${game.playerMe.getTeamName()}</p></td>
                    <td class="score-players players-my">
                        ${this.#renderPlayerStats(scoreData.scoreboard[myTeamIndex], game.players, roundNumber, false)}
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
                                <span class="color-me">${scoreData.firstHalfScore[scoreData.halfTimeRoundNumber === null ? myTeamIndex : otherTeamIndex]}</span><br>
                                <small>1st</small><br>
                                <span class="color-opponent">${scoreData.firstHalfScore[scoreData.halfTimeRoundNumber === null ? otherTeamIndex : myTeamIndex]}</span>
                            </td>
                            ${scoreData.halfTimeRoundNumber === null ? '' : `
                            <td>
                                <span class="color-me">${scoreData.secondHalfScore[myTeamIndex]}</span><br>
                                <small>2nd</small><br>
                                <span class="color-opponent">${scoreData.secondHalfScore[otherTeamIndex]}</span>
                            </td>`}
                        </table>
                    </td>
                    <td>
                        <div class="rounds-history">${this.#renderRoundsHistory(scoreData.history, scoreData.halfTimeRoundNumber, meIsAttacker)}</div>
                    </td>
                    <td style="width:135px">
                        <span class="color-me">$ ${scoreData.lossBonus[myTeamIndex]}</span><br>
                        <small>Round loss Bonus</small><br>
                        <span class="color-opponent">$ ${scoreData.lossBonus[otherTeamIndex]}</span>
                    </td>
                </tr>
            </table>
        </div>
        <div>
            <table>
                <tr>
                    <td class="score-opponent color-opponent">${scoreData.score[otherTeamIndex]}<p>Score ${game.playerMe.getOtherTeamName()}</p></td>
                    <td class="score-players players-opponent">
                        ${this.#renderPlayerStats(scoreData.scoreboard[otherTeamIndex], game.players, roundNumber, true)}
                    </td>
                </tr>
            </table>
        </div>
        `;
    }
}
