<?php

namespace Test\Movement;

use Closure;
use cs\Core\Floor;
use cs\Core\Player;
use cs\Core\Point;
use cs\Core\Setting;
use Test\BaseTestCase;

class MouseAngleTest extends BaseTestCase
{
    private function runAngle(int $angle, Closure $moveCallback, Point $startPos): Point
    {
        $playerCommands = [
            fn(Player $p) => $p->setPosition($startPos),
            fn(Player $p) => $p->getSight()->lookHorizontal($angle),
            $moveCallback,
            function (Player $p) use ($angle, $startPos) {
                if ($angle % 90 === 0) {
                    $this->assertTrue($startPos->x === $p->getPositionClone()->x || $startPos->z === $p->getPositionClone()->z);
                    return;
                }
                $this->assertNotSame($startPos->x, $p->getPositionClone()->x, "Angle: {$angle}");
                $this->assertNotSame($startPos->z, $p->getPositionClone()->z, "Angle: {$angle}");
            },
            $this->endGame(),
        ];

        $game = $this->createGame();
        $game->getWorld()->addFloor(new Floor(new Point(), 2 * Setting::moveDistancePerTick(), 2 * Setting::moveDistancePerTick()));
        $this->playPlayer($game, $playerCommands);
        return $game->getPlayer(1)->getPositionClone();
    }

    private function verifyPosition(Point $endPos, Point $startPos, ?bool $xBigger, ?bool $zBigger, int $angle): void
    {
        if ($xBigger === true) {
            $this->assertGreaterThan($startPos->x, $endPos->x, "Angle: {$angle}");
        } elseif ($xBigger === false) {
            $this->assertLessThan($startPos->x, $endPos->x, "Angle: {$angle}");
        } else {
            $this->assertSame($startPos->x, $endPos->x, "Angle: {$angle}");
        }

        if ($zBigger === true) {
            $this->assertGreaterThan($startPos->z, $endPos->z, "Angle: {$angle}");
        } elseif ($zBigger === false) {
            $this->assertLessThan($startPos->z, $endPos->z, "Angle: {$angle}");
        } else {
            $this->assertSame($startPos->z, $endPos->z, "Angle: {$angle}");
        }
    }

    protected function checkAngle(int $angleBase, ?bool $moveTowardsPositiveX, ?bool $moveTowardsPositiveZ): void
    {
        $startPos = new Point(2 * Setting::moveDistancePerTick(), 0, 2 * Setting::moveDistancePerTick());
        $endPos = $this->runAngle($angleBase, fn(Player $p) => $p->moveForward(), $startPos);
        $this->verifyPosition($endPos, $startPos, $moveTowardsPositiveX, $moveTowardsPositiveZ, $angleBase);
        $angle = $angleBase - 90;
        $this->assertPositionSame($this->runAngle($angle, fn(Player $p) => $p->moveRight(), $startPos), $endPos, " Angle: {$angle}");
        $angle = $angleBase - 180;
        $this->assertPositionSame($this->runAngle($angle, fn(Player $p) => $p->moveBackward(), $startPos), $endPos, " Angle: {$angle}");
        $angle = $angleBase - 270;
        $this->assertPositionSame($this->runAngle($angle, fn(Player $p) => $p->moveLeft(), $startPos), $endPos, " Angle: {$angle}");
    }

    /**
     * @return int[]
     */
    private function getRandomAngles(int $angleBase, int $count = 4): array
    {
        $angles = range($angleBase + 1, $angleBase + 89);
        shuffle($angles);
        return array_slice($angles, 0, $count);
    }

    public function testMouseAngle(): void
    {
        $angleBase = 0;
        $this->checkAngle($angleBase, null, true);
        foreach ($this->getRandomAngles($angleBase) as $angle) {
            $this->checkAngle($angle, true, true);
        }

        $angleBase = 90;
        $this->checkAngle($angleBase, true, null);
        foreach ($this->getRandomAngles($angleBase) as $angle) {
            $this->checkAngle($angle, true, false);
        }

        $angleBase = 180;
        $this->checkAngle($angleBase, null, false);
        foreach ($this->getRandomAngles($angleBase) as $angle) {
            $this->checkAngle($angle, false, false);
        }

        $angleBase = 270;
        $this->checkAngle($angleBase, false, null);
        foreach ($this->getRandomAngles($angleBase) as $angle) {
            $this->checkAngle($angle, false, true);
        }
    }

    public function testDiagonal(): void
    {
        $angle = rand(0, 360);
        $pos = $this->_testDiagonal($angle, true, true);

        $pos1 = $this->_testDiagonal($angle + 45, false, true);
        $this->assertPositionNotSame($pos, $pos1, "Angle: {$angle}");

        $pos2 = $this->_testDiagonal($angle + 90, false, true);
        $this->assertPositionSame($pos, $pos2, "Angle: {$angle}");

        $pos3 = $this->_testDiagonal($angle + 180, false, false);
        $this->assertPositionSame($pos, $pos3, "Angle: {$angle}");

        $pos4 = $this->_testDiagonal($angle - 90, true, false);
        $this->assertPositionSame($pos, $pos4, "Angle: {$angle}");

        $pos5 = $this->_testDiagonal($angle - 9, true, false);
        $this->assertPositionNotSame($pos, $pos5, "Angle: {$angle}");

        $pos190 = $this->_testDiagonal(190, false, true);
        $this->assertLessThan(100, $pos190->z);
        $this->assertGreaterThan(100, $pos190->x);
        $this->assertPositionSame($pos190, $this->_testDiagonal(190 + 180, true, false), "Angle: {$angle}");
    }

    public function _testDiagonal(int $angleHorizontal, bool $xPlus, bool $zPlus): Point
    {
        $playerCommands = [
            fn(Player $p) => $p->setPosition(new Point(100, 0, 100)),
            fn(Player $p) => $p->getSight()->lookAt($angleHorizontal, rand(-90, 90)),
            function (Player $p) use ($xPlus, $zPlus) {
                if ($xPlus) {
                    $p->moveRight();
                } else {
                    $p->moveLeft();
                }
                if ($zPlus) {
                    $p->moveForward();
                } else {
                    $p->moveBackward();
                }
            },
            $this->endGame(),
        ];

        $game = $this->createGame();
        $this->playPlayer($game, $playerCommands);
        return $game->getPlayer(1)->getPositionClone();
    }

}
