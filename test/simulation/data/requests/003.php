<?php

use cs\Core\Game;
use cs\Core\GameState;
use cs\Event\KillEvent;
use cs\Weapon\RifleAk;
use Test\Simulation\SimulationTester;

return new class extends SimulationTester {

    private int $killEvents = 0;

    public function onEvents(array $events): void
    {
        foreach ($events as $event) {
            if ($event instanceof KillEvent) {
                $this->killEvents++;
                $this->assertTrue($event->wasHeadShot());
            }
        }
    }

    public function onTickEnd(GameState $state, int $tick): void
    {
        if ($tick === 200) {
            $item = $state->getPlayer(1)->getEquippedItem();
            $this->assertInstanceOf(RifleAk::class, $item);
            $this->assertSame(20, $item->getAmmo());
            $this->assertSame(90, $item->getAmmoReserve());
        }
    }

    public function onGameEnd(Game $game): void
    {
        $this->assertSame(10, $this->killEvents);
        $this->assertSame(2, $game->getRoundNumber());
        $this->assertSame(1, $game->getScore()->getScoreAttackers());
        $this->assertSame(0, $game->getScore()->getScoreDefenders());
        $this->assertSame(10, $game->getScore()->getPlayerStat(1)->getKills());
        $this->assertSame(0, $game->getScore()->getPlayerStat(1)->getDeaths());
        $this->assertSame(1000, $game->getScore()->getPlayerStat(1)->getDamage());
        $this->assertSame(0, $game->getScore()->getPlayerStat(2)->getKills());
        $this->assertSame(1, $game->getScore()->getPlayerStat(2)->getDeaths());
        $this->assertSame(0, $game->getScore()->getPlayerStat(2)->getDamage());
        $this->assertSame(RifleAk::killAward * 10 + 3250, $game->getPlayer(1)->getMoney());
        $item = $game->getPlayer(1)->getEquippedItem();
        $this->assertInstanceOf(RifleAk::class, $item);
        $this->assertSame(30, $item->getAmmo());
        $this->assertSame(90, $item->getAmmoReserve());
    }

};
