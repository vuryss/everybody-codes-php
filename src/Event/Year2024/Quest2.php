<?php

declare(strict_types=1);

namespace App\Event\Year2024;

use App\Event\QuestInterface;

class Quest2 implements QuestInterface
{
    public function testPart1(): iterable
    {
        yield '4' => <<<'INPUT'
            WORDS:THE,OWE,MES,ROD,HER
            
            AWAKEN THE POWER ADORNED WITH THE FLAMES BRIGHT IRE
            INPUT;

        yield '3' => <<<'INPUT'
            WORDS:THE,OWE,MES,ROD,HER
            
            THE FLAME SHIELDED THE HEART OF THE KINGS
            INPUT;

        yield '2' => <<<'INPUT'
            WORDS:THE,OWE,MES,ROD,HER
            
            POWE PO WER P OWE R
            INPUT;

        yield '3' => <<<'INPUT'
            WORDS:THE,OWE,MES,ROD,HER
            
            THERE IS THE END
            INPUT;
    }

    public function testPart2(): iterable
    {
        yield '37' => <<<'INPUT'
            WORDS:THE,OWE,MES,ROD,HER
            
            AWAKEN THE POWE ADORNED WITH THE FLAMES BRIGHT IRE
            THE FLAME SHIELDED THE HEART OF THE KINGS
            POWE PO WER P OWE R
            THERE IS THE END
            INPUT;
    }

    public function testPart3(): iterable
    {
        yield '10' => <<<'INPUT'
            WORDS:THE,OWE,MES,ROD,RODEO
            
            HELWORLT
            ENIGWDXL
            TRODEOAL
            INPUT;
    }

    public function solvePart1(string $input): string|int
    {
        $parts = explode("\n\n", $input);
        $words = explode(',', substr($parts[0], 6));
        $count = 0;

        foreach ($words as $word) {
            $count += substr_count($parts[1], $word);
        }

        return $count;
    }

    public function solvePart2(string $input): string|int
    {
        $parts = explode("\n\n", $input);
        $words = explode(',', substr($parts[0], 6));
        $words = array_merge($words, array_map('strrev', $words));
        $indexes = [];

        foreach ($words as $word) {
            $index = 0;

            while (false !== $next = strpos($parts[1], $word, $index)) {
                for ($i = 0; $i < strlen($word); $i++) {
                    $indexes[$next + $i] = true;
                }

                $index++;
            }
        }

        return count($indexes);
    }

    public function solvePart3(string $input): string|int
    {
        $parts = explode("\n\n", $input);
        $words = explode(',', substr($parts[0], 6));
        $words = array_merge($words, array_map('strrev', $words));
        $indexes = [];
        $lines = [];
        $columns = [];

        foreach (explode("\n", $parts[1]) as $i => $line) {
            $lines[] = $line . $line;

            foreach (str_split($line) as $j => $char) {
                $columns[$j] ??= '';
                $columns[$j] .= $char;
            }
        }

        $lineLength = strlen($lines[0]) / 2;

        foreach ($words as $word) {
            foreach ($lines as $y => $line) {
                $index = 0;

                while (false !== $next = strpos($line, $word, $index++)) {
                    for ($i = 0; $i < strlen($word); $i++) {
                        $indexes[$y][($next + $i) % $lineLength] = true;
                    }
                }
            }

            foreach ($columns as $x => $column) {
                $index = 0;

                while (false !== $next = strpos($column, $word, $index++)) {
                    for ($i = 0; $i < strlen($word); $i++) {
                        $indexes[$next + $i][$x] = true;
                    }
                }
            }
        }

        return count($indexes, COUNT_RECURSIVE) - count($indexes);
    }
}
