import {ItemId, ItemIdToIcon} from "../Enums.js";

export class KillFeed {
    #element
    #scoreBoard

    constructor(scoreBoard, killFeedElement) {
        this.#scoreBoard = scoreBoard
        this.#element = killFeedElement
    }

    showKill(playerCulprit, playerDead, wasHeadshot, playerMe, killedItemId) {
        let killedByBomb = false
        this.#scoreBoard.updatePlayerIsDead(playerDead)
        if (playerCulprit.id === playerDead.id) { // suicide or bomb
            if (killedItemId === ItemId.SolidSurface) { // suicide
                this.#scoreBoard.updatePlayerKills(playerDead, -1)
            } else if (killedItemId === ItemId.Bomb) { // bomb
                killedByBomb = true
            } else {
                throw new Error("New killing item?")
            }
        } else if (playerCulprit.isAttacker === playerDead.isAttacker) { // team kill
            this.#scoreBoard.updatePlayerKills(playerCulprit, -1)
        } else {
            this.#scoreBoard.updatePlayerKills(playerCulprit, 1)
        }

        const culprit = document.createElement('span')
        const culpritOnMyTeam = (playerCulprit.isAttacker === playerMe.isAttacker)
        culprit.classList.add(culpritOnMyTeam ? 'team-me' : 'team-opponent')
        culprit.innerText = killedByBomb ? ItemIdToIcon[ItemId.Bomb] : this.#scoreBoard.getPlayerName(playerCulprit, playerMe)

        const dead = document.createElement('span')
        const deadOnyMyTeam = (playerDead.isAttacker === playerMe.isAttacker)
        dead.classList.add(deadOnyMyTeam ? 'team-me' : 'team-opponent')
        dead.innerText = this.#scoreBoard.getPlayerName(playerDead, playerMe)

        const parentElement = this.#element
        if (parentElement.children.length > 4) {
            parentElement.children[0].remove()
        }

        const row = document.createElement('p')
        const line = document.createElement('span')
        let shouldHighlight = (playerCulprit.id === playerMe.id || playerDead.id === playerMe.id)
        if (shouldHighlight) {
            row.classList.add('highlight')
        }
        let headshot = (wasHeadshot ? ' ⌖' : '')
        line.append(culprit)
        line.append(` ${ItemIdToIcon[killedItemId]}${headshot} `)
        line.append(dead)
        row.append(line)
        parentElement.append(row)

        setTimeout(() => row.remove(), 3000)
    }
}
