<?php

declare(strict_types=1);

namespace App\Event\Year2024;

use App\Event\QuestInterface;

class Quest5 implements QuestInterface
{
    public function testPart1(): iterable
    {
        yield '2323' => <<<'INPUT'
            2 3 4 5
            3 4 5 2
            4 5 2 3
            5 2 3 4
            INPUT;
    }

    public function testPart2(): iterable
    {
        yield '50877075' => <<<'INPUT'
            2 3 4 5
            6 7 8 9
            INPUT;
    }

    public function testPart3(): iterable
    {
        yield '6584' => <<<'INPUT'
            2 3 4 5
            6 7 8 9
            INPUT;
    }

    public function solvePart1(string $input): string|int
    {
        $columns = $this->constructColumns($input);

        for ($round = 0; $round < 10; $round++) {
            $columns = $this->playRound($columns, $round);
        }

        return implode('', array_column($columns, 0));
    }

    public function solvePart2(string $input): string|int
    {
        $columns = $this->constructColumns($input);
        $shoutCounts = [];
        $round = 0;

        while (true) {
            $columns = $this->playRound($columns, $round++);
            $number = implode('', array_column($columns, 0));

            $shoutCounts[$number] ??= 0;
            $shoutCounts[$number]++;

            if (2024 === $shoutCounts[$number]) {
                return (int) $number * $round;
            }
        }
    }

    public function solvePart3(string $input): string|int
    {
        $columns = $this->constructColumns($input);
        $numbers = [];
        $round = 0;
        $states = [];

        while (true) {
            $state = json_encode($columns);

            if (isset($states[$state])) {
                break;
            }

            $states[$state] = true;
            $columns = $this->playRound($columns, $round++);
            $number = implode('', array_column($columns, 0));
            $numbers[$number] = true;
        }

        return max(array_keys($numbers));
    }

    private function constructColumns(string $input): array
    {
        $lines = explode("\n", $input);
        $columns = [];

        foreach ($lines as $index => $line) {
            foreach (explode(' ', $line) as $columnIndex => $char) {
                $columns[$columnIndex][$index] = (int) $char;
            }
        }

        return $columns;
    }

    private function playRound(array $columns, int $round): array
    {
        $clapperColumn = $round % 4;
        $clapper = array_shift($columns[$clapperColumn]);
        $targetColumnIndex = ($clapperColumn + 1) % 4;
        $targetColumnSize = count($columns[$targetColumnIndex]);
        $newPosition = abs(($clapper % ($targetColumnSize * 2)) - 1);

        if ($newPosition > $targetColumnSize) {
            $newPosition = $targetColumnSize * 2 - $newPosition;
        }

        array_splice($columns[$targetColumnIndex], $newPosition, 0, $clapper);

        return $columns;
    }
}
