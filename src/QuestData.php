<?php

declare(strict_types=1);

namespace App;

readonly class QuestData
{
    public function __construct(
        public string $input1,
        public ?string $input2,
        public ?string $input3,
        public ?string $answer1,
        public ?string $answer2,
        public ?string $answer3,
    ) {
    }
}
