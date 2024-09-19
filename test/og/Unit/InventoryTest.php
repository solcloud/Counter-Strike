<?php

namespace Test\Unit;

use cs\Core\Inventory;
use cs\Enum\InventorySlot;
use cs\Equipment\Incendiary;
use Test\BaseTest;

final class InventoryTest extends BaseTest
{

    public function testGrenadeLastEquippedSlots(): void
    {
        $inventory = new Inventory(false);
        $this->assertSame(InventorySlot::getGrenadeSlotIds(), $inventory->getLastEquippedGrenadeSlots());

        $lastGrenadeEquippedSlots = $inventory->getLastEquippedGrenadeSlots();
        $this->assertSame(InventorySlot::SLOT_GRENADE_SMOKE->value, array_shift($lastGrenadeEquippedSlots));

        $this->assertTrue($inventory->pickup(new Incendiary()));
        $this->assertNotNull($inventory->equip(InventorySlot::SLOT_GRENADE_MOLOTOV));
        $lastGrenadeEquippedSlots = $inventory->getLastEquippedGrenadeSlots();
        $this->assertNotSame(InventorySlot::getGrenadeSlotIds(), $lastGrenadeEquippedSlots);
        $expectedSlots = [InventorySlot::SLOT_GRENADE_MOLOTOV->value];
        foreach (InventorySlot::getGrenadeSlotIds() as $slotId) {
            if ($slotId === InventorySlot::SLOT_GRENADE_MOLOTOV->value) {
                continue;
            }
            $expectedSlots[] = $slotId;
        }
        $this->assertSame($expectedSlots, $lastGrenadeEquippedSlots);

        $inventory->removeSlot(InventorySlot::SLOT_GRENADE_MOLOTOV->value);
        $lastGrenadeEquippedSlots = $inventory->getLastEquippedGrenadeSlots();
        $this->assertSame(InventorySlot::SLOT_GRENADE_SMOKE->value, array_shift($lastGrenadeEquippedSlots));
    }

}
