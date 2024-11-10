<?php

declare(strict_types=1);

namespace App\Event;

use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['app.event_quest'])]
interface QuestInterface
{
    public function testPart1(): iterable;
    public function testPart2(): iterable;
    public function testPart3(): iterable;

    /**
     * @throws Exception
     */
    public function solvePart1(string $input): string|int;

    /**
     * @throws Exception
     */
    public function solvePart2(string $input): string|int;

    /**
     * @throws Exception
     */
    public function solvePart3(string $input): string|int;
}