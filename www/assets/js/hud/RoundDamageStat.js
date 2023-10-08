import {ColorNames} from "../Enums.js";

export class RoundDamageStat {
    #element

    constructor(element) {
        this.#element = element
    }

    update(damage, enemyPlayers) {
        if (damage === null || !enemyPlayers.length) {
            this.#element.innerHTML = ''
            return
        }

        let id, count, sum, deadPlayers = [], alivePlayers = []
        enemyPlayers.forEach((player) => {
            id = player.getId()
            let data = {
                name: ColorNames[player.getColorIndex()],
                health: player.data.health,
                damageDid: 0,
                damageGot: 0,
            }
            if (damage.did[id]) {
                count = damage.did[id].length
                sum = damage.did[id].reduce((acc, val) => acc += val)
                data.damageDid = `${sum} in ${count}`
            }
            if (damage.got[id]) {
                count = damage.got[id].length
                sum = damage.got[id].reduce((acc, val) => acc += val)
                data.damageGot = `${sum} in ${count}`
            }

            if (player.isAlive()) {
                alivePlayers.push(data)
            } else {
                deadPlayers.push(data)
            }
        })

        let html = ''
        ;[...alivePlayers, ...deadPlayers].forEach((row) => {
            html += `<tr>
                <td>${row.name} (${row.health} hp)</td>
                <td${row.damageDid === 0 ? '' : ' class="highlight"'}>${row.damageDid}</td>
                <td${row.damageGot === 0 ? '' : ' class="highlight"'}>${row.damageGot}</td>
            </tr>
            `
        })

        this.#element.innerHTML = `<table>
            <tr>
                <th>Enemy Name</th>
                <th>Harm did</th>
                <th>Harm got</th>
            </tr>
            ${html}
        </table>
        `
    }

}
