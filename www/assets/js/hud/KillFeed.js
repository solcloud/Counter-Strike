export class KillFeed {
    #element
    #scoreBoard

    constructor(scoreBoard, killFeedElement) {
        this.#scoreBoard = scoreBoard
        this.#element = killFeedElement
    }

    showKill(playerCulprit, playerDead, wasHeadshot, playerMe, killedItemId) {
        this.#scoreBoard.updatePlayerIsDead(playerDead)
        if (playerCulprit.id === playerDead.id) { // suicide
            this.#scoreBoard.updatePlayerKills(playerDead, -1)
        } else if (playerCulprit.isAttacker === playerDead.isAttacker) { // team kill
            this.#scoreBoard.updatePlayerKills(playerCulprit, -1)
        } else {
            this.#scoreBoard.updatePlayerKills(playerCulprit, 1)
        }

        const culprit = document.createElement('span')
        const culpritOnMyTeam = (playerCulprit.isAttacker === playerMe.isAttacker)
        culprit.classList.add(culpritOnMyTeam ? 'team-me' : 'team-opponent')
        culprit.innerText = this.#scoreBoard.getPlayerName(playerCulprit, playerMe)

        const dead = document.createElement('span')
        const deadOnyMyTeam = (playerDead.isAttacker === playerMe.isAttacker)
        dead.classList.add(deadOnyMyTeam ? 'team-me' : 'team-opponent')
        dead.innerText = this.#scoreBoard.getPlayerName(playerDead, playerMe)

        const parentElement = this.#element
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

        setTimeout(() => row.remove(), 3000)
    }
}
