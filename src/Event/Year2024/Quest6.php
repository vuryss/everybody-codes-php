<?php

declare(strict_types=1);

namespace App\Event\Year2024;

use App\Event\QuestInterface;
use Ds\Queue;

class Quest6 implements QuestInterface
{
    public function testPart1(): iterable
    {
        yield 'RRB@' => <<<'INPUT'
            RR:A,B,C
            A:D,E
            B:F,@
            C:G,H
            D:@
            E:@
            F:@
            G:@
            H:@
            INPUT;
    }

    public function testPart2(): iterable
    {
        return [];
    }

    public function testPart3(): iterable
    {
        return [];
    }

    public function solvePart1(string $input): string|int
    {
        return $this->solve($input, fn ($string) => $string);
    }

    public function solvePart2(string $input): string|int
    {
        return $this->solve($input, fn ($string) => $string[0]);
    }

    public function solvePart3(string $input): string|int
    {
        return $this->solve($input, fn ($string) => $string[0]);
    }

    private function solve(string $input, callable $pathMap): string
    {
        $nodes = [];

        foreach (explode("\n", $input) as $line) {
            [$node, $children] = explode(':', $line);
            $nodes[$node] = explode(',', $children);
        }

        /** @var Queue<array{0: string, 1: array<string>}> $queue */
        $queue = new Queue();
        $queue->push(['RR', ['RR']]);
        $paths = [];

        while (!$queue->isEmpty()) {
            [$node, $path] = $queue->pop();

            if ($node === '@') {
                $fullPath = implode('', array_map($pathMap, $path));
                $paths[$fullPath] = count($path);
                continue;
            }

            foreach ($nodes[$node] ?? [] as $child) {
                if (in_array($child, $path)) {
                    continue;
                }

                $queue->push([$child, [...$path, $child]]);
            }
        }

        $pathsCount = array_count_values($paths);
        $minPathCount = array_search(min($pathsCount), $pathsCount);

        return array_search($minPathCount, $paths);
    }
}
