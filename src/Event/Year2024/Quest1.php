<?php

declare(strict_types=1);

namespace App\Event\Year2024;

use App\Event\QuestInterface;

class Quest1 implements QuestInterface
{
    public function testPart1(): iterable
    {
        yield '5' => <<<'INPUT'
            ABBAC
            INPUT;
    }

    public function testPart2(): iterable
    {
        yield '28' => <<<'INPUT'
            AxBCDDCAxD
            INPUT;
    }

    public function testPart3(): iterable
    {
        yield '30' => <<<'INPUT'
            xBxAAABCDxCC
            INPUT;
    }

    public function solvePart1(string $input): string|int
    {
        return array_sum(
            array_map(
                static fn ($char): int => match ($char) { 'A' => 0, 'B' => 1, 'C' => 3 },
                str_split($input),
            ),
        );
    }

    public function solvePart2(string $input): string|int
    {
        $sum = 0;

        for ($i = 0; $i < strlen($input); $i += 2) {
            $enemies = str_replace('x', '', substr($input, $i, 2));
            $sum += match (strlen($enemies)) {
                0 => 0,
                1 => match ($enemies) { 'A' => 0, 'B' => 1, 'C' => 3, 'D' => 5 },
                2 => array_sum(
                    array_map(
                        static fn ($char): int => match ($char) { 'A' => 1, 'B' => 2, 'C' => 4, 'D' => 6 },
                        str_split($enemies),
                    ),
                ),
            };
        }

        return $sum;
    }

    public function solvePart3(string $input): string|int
    {
        $sum = 0;

        for ($i = 0; $i < strlen($input); $i += 3) {
            $enemies = str_replace('x', '', substr($input, $i, 3));
            $sum += match (strlen($enemies)) {
                0 => 0,
                1 => match ($enemies) { 'A' => 0, 'B' => 1, 'C' => 3, 'D' => 5 },
                2 => array_sum(
                    array_map(
                        static fn ($char): int => match ($char) { 'A' => 1, 'B' => 2, 'C' => 4, 'D' => 6 },
                        str_split($enemies),
                    ),
                ),
                3 => array_sum(
                    array_map(
                        static fn ($char): int => match ($char) { 'A' => 2, 'B' => 3, 'C' => 5, 'D' => 7 },
                        str_split($enemies),
                    ),
                ),
            };
        }

        return $sum;
    }
}
