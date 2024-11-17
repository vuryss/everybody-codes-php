<?php

declare(strict_types=1);

namespace App\Event\Year2024;

use App\Event\QuestInterface;
use App\Util\StringUtil;

class Quest4 implements QuestInterface
{
    public function testPart1(): iterable
    {
        yield '10' => <<<'INPUT'
            3
            4
            7
            8
            INPUT;
    }

    public function testPart2(): iterable
    {
        return [];
    }

    public function testPart3(): iterable
    {
        yield '8' => <<<'INPUT'
            2
            4
            5
            6
            8
            INPUT;
    }

    public function solvePart1(string $input): string|int
    {
        $numbers = StringUtil::extractIntegers($input);
        $min = min($numbers);
        $numbers = array_map(static fn ($number) => $number - $min, $numbers);

        return (int) array_sum($numbers);
    }

    public function solvePart2(string $input): string|int
    {
        $numbers = StringUtil::extractIntegers($input);
        $min = min($numbers);
        $numbers = array_map(static fn ($number) => $number - $min, $numbers);

        return (int) array_sum($numbers);
    }

    public function solvePart3(string $input): string|int
    {
        $numbers = StringUtil::extractIntegers($input);
        $guess = (int) (array_sum($numbers) / count($numbers));
        $min = PHP_INT_MAX;
        $d = 1;

        do {
            $hits = array_sum(array_map(static fn ($number) => abs($number - $guess), $numbers));
            $min = min($min, $hits);

            if ($hits > $min) {
                if ($d > 0) {
                    $d = -1;
                } else {
                    break;
                }
            }
        } while ($guess += $d);

        return (int) $min;
    }
}
