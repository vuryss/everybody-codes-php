<?php

declare(strict_types=1);

namespace App\Event;

class EventQuestRegistry
{
    private array $questsByYear;

    public function __construct()
    {
        $this->questsByYear = [];
    }

    public function addQuest(int $year, int $day, QuestInterface $dayObject): self
    {
        $this->questsByYear[$year][$day] = $dayObject;

        return $this;
    }

    public function getQuestInYear(int $year, int $day): ?QuestInterface
    {
        return $this->questsByYear[$year][$day] ?? null;
    }
}
