import * as Enum from "../Enums.js";

export class BuyMenu {
    #element
    #lastPlayerMoney = null

    constructor(buyMenuElement) {
        this.#element = buyMenuElement
    }

    refresh(playerData, teamName) {
        if (this.#lastPlayerMoney === playerData.money && this.#element.innerHTML !== '') {
            return
        }

        const money = playerData.money
        const isAttacker = playerData.isAttacker
        this.#lastPlayerMoney = money

        this.#element.innerHTML = `
            <p class="title">${teamName} Buy Store. Your money balance $ <strong>${money}</strong></p>
            <h3>Equipment</h3>
            <p${money < 1000 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.KEVLAR_BODY_AND_HEAD}" class="hud-action action-buy">Buy Kevlar + Helmet for $ 1,000</a></p>
            <p${money < 650 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.KEVLAR_BODY}" class="hud-action action-buy">Buy Kevlar for $ 650</a></p>
            ${isAttacker
            ? ``
            : `<p${money < 400 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.DEFUSE_KIT}" class="hud-action action-buy">Buy Defuse Kit for $ 400</a></p>`
        }
            <h3>Pistols</h3>
        ${isAttacker
            ? `<p${money < 200 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.PISTOL_GLOCK}" class="hud-action action-buy">Buy Glock for $ 200</a></p>`
            : `<p${money < 200 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.PISTOL_USP}" class="hud-action action-buy">Buy USP for $ 200</a></p>`
        }
            <p${money < 250 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.PISTOL_P250}" class="hud-action action-buy">Buy P-250 for $ 250</a></p>
            <h3>Rifles</h3>
        ${isAttacker
            ? `<p${money < 2700 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.RIFLE_AK}" class="hud-action action-buy">Buy AK-47 for $ 2,700</a></p>`
            : `<p${money < 3100 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.RIFLE_M4A4}" class="hud-action action-buy">Buy M4-A1 for $ 3,100</a></p>`
        }
        `;
    }
}
