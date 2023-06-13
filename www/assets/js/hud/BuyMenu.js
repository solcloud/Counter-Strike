import * as Enum from "../Enums.js";

export class BuyMenu {
    #element
    #priceFormatter
    #lastPlayerMoney = null

    constructor(buyMenuElement) {
        this.#element = buyMenuElement
        this.#priceFormatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0,
        });
    }

    #formatPrice(price) {
        return this.#priceFormatter.format(price)
    }

    refresh(playerData, teamName) {
        if (this.#lastPlayerMoney === playerData.money && this.#element.innerHTML !== '') {
            return
        }

        const money = playerData.money
        const isAttacker = playerData.isAttacker
        const cannotBuyKevlar = (playerData.armorType === Enum.ArmorType.BODY_AND_HEAD && playerData.armor === 100)
        const cannotBuyBodyKevlar = (playerData.armorType === Enum.ArmorType.BODY && playerData.armor === 100)
        let kevlarHeadPrice = 1000
        if (playerData.armorType === Enum.ArmorType.BODY && playerData.armor === 100) {
            kevlarHeadPrice = 350
        } else if (playerData.armorType === Enum.ArmorType.BODY_AND_HEAD) {
            kevlarHeadPrice = 650
        }
        this.#lastPlayerMoney = money

        this.#element.innerHTML = `
            <p class="title">${teamName} Buy Store. Your money balance $ <strong>${money}</strong></p>
            <h3>Equipment</h3>
        ${cannotBuyKevlar
            ? ''
            : `
                <p${money < kevlarHeadPrice ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.KEVLAR_BODY_AND_HEAD}" class="hud-action action-buy">Kevlar + Helmet for ${this.#formatPrice(kevlarHeadPrice)}</a></p>
                ${cannotBuyBodyKevlar ? '' : `<p${money < 650 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.KEVLAR_BODY}" class="hud-action action-buy">Kevlar for ${this.#formatPrice(650)}</a></p>`}
            `
        }
        ${!isAttacker && playerData.slots[Enum.InventorySlot.SLOT_KIT] === undefined
            ? `<p${money < 400 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.DEFUSE_KIT}" class="hud-action action-buy">Defuse Kit for ${this.#formatPrice(400)}</a></p>`
            : ``
        }
            <h3>Grenades</h3>
            <div class="menu-grenades">
        <p${money < 200 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.GRENADE_FLASH}" class="hud-action action-buy">Flash for ${this.#formatPrice(200)}</a></p>
        ${playerData.slots[Enum.InventorySlot.SLOT_GRENADE_SMOKE] === undefined
            ? `<p${money < 300 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.GRENADE_SMOKE}" class="hud-action action-buy">Smoke for ${this.#formatPrice(300)}</a></p>`
            : ``
        }
        ${playerData.slots[Enum.InventorySlot.SLOT_GRENADE_HE] === undefined
            ? `<p${money < 300 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.GRENADE_HE}" class="hud-action action-buy">Frag for ${this.#formatPrice(300)}</a></p>`
            : ``
        }
        ${playerData.slots[Enum.InventorySlot.SLOT_GRENADE_MOLOTOV] === undefined
            ? `${isAttacker
                ? `<p${money < 400 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.GRENADE_MOLOTOV}" class="hud-action action-buy">Molotov for ${this.#formatPrice(400)}</a></p>`
                : `<p${money < 600 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.GRENADE_INCENDIARY}" class="hud-action action-buy">Incendiary for ${this.#formatPrice(600)}</a></p>`
            }` : ``
        }
        ${playerData.slots[Enum.InventorySlot.SLOT_GRENADE_DECOY] === undefined
            ? `<p${money < 50 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.GRENADE_DECOY}" class="hud-action action-buy">Decoy for ${this.#formatPrice(50)}</a></p>`
            : ``
        }
            </div>
            <h3>Pistols</h3>
        ${isAttacker
            ? `<p${money < 200 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.PISTOL_GLOCK}" class="hud-action action-buy">Glock for ${this.#formatPrice(200)}</a></p>`
            : `<p${money < 200 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.PISTOL_USP}" class="hud-action action-buy">USP for ${this.#formatPrice(200)}</a></p>`
        }
            <p${money < 250 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.PISTOL_P250}" class="hud-action action-buy">P-250 for ${this.#formatPrice(250)}</a></p>
            <h3>Rifles</h3>
        ${isAttacker
            ? `<p${money < 2700 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.RIFLE_AK}" class="hud-action action-buy">AK-47 for ${this.#formatPrice(2700)}</a></p>`
            : `<p${money < 3100 ? ' class="disabled"' : ''}><a data-buy-menu-item-id="${Enum.BuyMenuItem.RIFLE_M4A4}" class="hud-action action-buy">M4-A1 for ${this.#formatPrice(3100)}</a></p>`
        }
        `;
    }
}
