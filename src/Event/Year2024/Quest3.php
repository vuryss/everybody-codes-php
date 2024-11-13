<?php

declare(strict_types=1);

namespace App\Event\Year2024;

use App\Event\QuestInterface;
use App\Util\Point2D;
use App\Util\StringUtil;

class Quest3 implements QuestInterface
{
    public function testPart1(): iterable
    {
        yield '35' => <<<'INPUT'
            ..........
            ..###.##..
            ...####...
            ..######..
            ..######..
            ...####...
            ..........
            INPUT;
    }

    public function testPart2(): iterable
    {
        return [];
    }

    public function testPart3(): iterable
    {
        yield '29' => <<<'INPUT'
            ..........
            ..###.##..
            ...####...
            ..######..
            ..######..
            ...####...
            ..........
            INPUT;
    }

    public function solvePart1(string $input): string|int
    {
        return $this->getNumberOfRemovedBlocks($input, false);
    }

    public function solvePart2(string $input): string|int
    {
        return $this->getNumberOfRemovedBlocks($input, false);
    }

    public function solvePart3(string $input): string|int
    {
        return $this->getNumberOfRemovedBlocks($input, true);
    }

    private function getNumberOfRemovedBlocks(string $input, bool $considerAllDirections): int
    {
        $input = str_replace('#', '1', $input);
        $grid = StringUtil::inputToGridOfChars($input);
        $change = true;
        $newGrid = $grid;

        while (true === $change) {
            $change = false;

            for ($y = 0; $y < count($grid); $y++) {
                for ($x = 0; $x < count($grid[0]); $x++) {
                    if ('.' !== $grid[$y][$x]) {
                        $point = new Point2D($x, $y);
                        $currentDepth = (int) $grid[$y][$x];
                        $adjacentPoints = $considerAllDirections ? $point->surrounding() : $point->adjacent();

                        foreach ($adjacentPoints as $adjacentPoint) {
                            if (
                                !isset($grid[$adjacentPoint->y][$adjacentPoint->x])
                                || (int) $grid[$adjacentPoint->y][$adjacentPoint->x] !== $currentDepth
                            ) {
                                continue 2;
                            }
                        }

                        $newGrid[$y][$x] = $currentDepth + 1;
                        $change = true;
                    }
                }
            }

            $grid = $newGrid;
        }

        $count = 0;

        foreach ($grid as $row) {
            foreach ($row as $cell) {
                $count += is_int($cell) || ctype_digit($cell) ? (int) $cell : 0;
            }
        }

        return $count;
    }
}
