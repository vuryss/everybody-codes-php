<?php

declare(strict_types=1);

namespace App;

readonly class QuestId
{
    public function __construct(
        public int $event,
        public ?int $quest = null,
    ) {
        if ($event < 2024) {
            throw new \InvalidArgumentException('There are no Everybody Codes events before 2024');
        }

        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');

        if ($event > $currentYear) {
            throw new \InvalidArgumentException('No quests available for year ' . $event);
        }

        if ($currentMonth < 11 && $event === $currentYear) {
            throw new \InvalidArgumentException('The event has not started yet for this year');
        }

        if (null === $quest) {
            return;
        }

        if ($quest < 1 || $quest > 20) {
            throw new \InvalidArgumentException('Invalid quest number');
        }
    }
}
