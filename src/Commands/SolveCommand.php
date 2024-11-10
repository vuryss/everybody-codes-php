<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Commands;

use App\Event\QuestInterface;
use App\Event\EventQuestRegistry;
use App\QuestData;
use App\DataResolver;
use App\QuestId;
use Exception;
use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('solve', 'Solve a given quest of Everybody Codes.')]
class SolveCommand extends Command
{
    private OutputInterface $output;

    public function __construct(
        private readonly EventQuestRegistry $eventQuestRegistry,
        private readonly DataResolver $dataResolver,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'event',
                mode: InputArgument::REQUIRED,
                description: 'Which Everybody Codes event to use.',
            )
            ->addArgument(
                name: 'quest',
                mode: InputArgument::OPTIONAL,
                description: 'Quest to solve',
            )
            ->addOption(
                name: 'test',
                mode: InputOption::VALUE_NONE,
                description: 'Run the tests instead of AoC user input.'
            )
            ->addOption(
                name: 'validate',
                mode: InputOption::VALUE_NONE,
                description: 'Validate the answers in already solved Everybody Codes quest.',
            )
        ;
    }

    /**
     * @throws Exception
     */
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->validateInput($input);
        $questId = new QuestId(
            event: (int) $input->getArgument('event'),
            quest: null === $input->getArgument('quest') ? null : (int) $input->getArgument('quest'),
        );
        $runTests = (bool) $input->getOption('test');
        $validate = (bool) $input->getOption('validate');

        if ($runTests) {
            $this->executeTests($questId);

            return Command::SUCCESS;
        }

        if ($validate) {
            $this->validateSolutions($questId);

            return Command::SUCCESS;
        }

        $this->executeSolutions($questId);

        return Command::SUCCESS;
    }

    private function validateInput(InputInterface $input): void
    {
        $year = (int) $input->getArgument('event');
        $quest = (int) $input->getArgument('quest');

        if ($year < 2024 || $year > (int) date('Y')) {
            throw new LogicException('Invalid event year given. Allowed values are between 2024 and ' . date('Y'));
        }

        if ($quest < 1 || $quest > 20) {
            throw new LogicException('Invalid event quest given. Allowed values are between 1 and 20');
        }
    }

    /**
     * @throws Exception
     */
    private function executeTests(QuestId $questId): void
    {
        $this->output->writeln('<comment> Running with test input </comment>');

        if (null === $questId->quest) {
            for ($quest = 1; $quest <= 20; $quest++) {
                $this->runTestsForQuest(new QuestId($questId->event, $quest));
            }

            return;
        }

        $this->runTestsForQuest($questId);
    }

    /**
     * @throws Exception
     */
    private function runTestsForQuest(QuestId $questId): void
    {
        $solution = $this->getSolutionForQuest($questId);

        $table = new Table($this->output->section());
        $table->addRow([new TableCell('Quest ' . $questId->quest, ['colspan' => 3, 'style' => new TableCellStyle(['align' => 'center'])])]);
        $table->addRow(new TableSeparator());

        $this->runTests($solution->testPart1(), fn ($input) => (string) $solution->solvePart1($input), $table, 'Part 1');
        $table->addRow(new TableSeparator());
        $this->runTests($solution->testPart2(), fn ($input) => (string) $solution->solvePart2($input), $table, 'Part 2');
        $table->addRow(new TableSeparator());
        $this->runTests($solution->testPart3(), fn ($input) => (string) $solution->solvePart3($input), $table, 'Part 3');

        $table->render();
    }

    private function runTests(iterable $tests, callable $solveFn, Table $table, string $part): void
    {
        $testNumber = 1;

        foreach ($tests as $expectedResult => $testInput) {
            $actualResult = $solveFn($testInput);

            if ($expectedResult === $actualResult) {
                $table->addRow([$part, 'Test ' . $testNumber++, '<info>Success</info>']);
            } else {
                $table->addRow([
                    $part,
                    'Test ' . $testNumber++,
                    '<error>Expected: ' . $expectedResult . ' Received: ' . $actualResult . '</error>'
                ]);
            }
        }
    }

    private function validateSolutions(QuestId $questId): void
    {
        $this->output->writeln('<comment> Running validation with quest input </comment>');

        if (null === $questId->quest) {
            for ($quest = 1; $quest <= 20; $quest++) {
                $this->validateSolutionForQuest(new QuestId($questId->event, $quest));
            }

            return;
        }

        $this->validateSolutionForQuest($questId);
    }

    private function validateSolutionForQuest(QuestId $questId): void
    {
        $solution = $this->getSolutionForQuest($questId);
        $questData = $this->getDataForQuest($questId);

        $part1Result = (string) $solution->solvePart1($questData->input1);
        $part2Result = (string) $solution->solvePart2($questData->input2);
        $part3Result = (string) $solution->solvePart3($questData->input3);

        $table = new Table($this->output->section());
        $table->addRow([new TableCell('Quest ' . $questId->quest, ['colspan' => 2, 'style' => new TableCellStyle(['align' => 'center'])])]);
        $table->addRow(new TableSeparator());

        $table->addRow([
            'Part 1',
            $part1Result === $questData->answer1 ? '<info>Success</info>' : '<error>Failed, expected: ' . $questData->answer1 . ' received: ' . $part1Result . '</error>',
        ]);
        $table->addRow([
            'Part 2',
            $part2Result === $questData->answer2 ? '<info>Success</info>' : '<error>Failed, expected: ' . $questData->answer2 . ' received: ' . $part2Result . '</error>',
        ]);
        $table->addRow([
            'Part 3',
            $part3Result === $questData->answer3 ? '<info>Success</info>' : '<error>Failed, expected: ' . $questData->answer3 . ' received: ' . $part3Result . '</error>',
        ]);

        $table->render();
    }

    private function executeSolutions(QuestId $questId): void
    {
        $this->output->writeln('<comment> Running solution with quest input </comment>');

        if (null === $questId->quest) {
            for ($quest = 1; $quest <= 20; $quest++) {
                $this->solveQuest(new QuestId($questId->event, $quest));
            }

            return;
        }

        $this->solveQuest($questId);
    }

    private function solveQuest(QuestId $questId): void
    {
        $solution = $this->getSolutionForQuest($questId);
        $questInput = $this->getDataForQuest($questId);

        $table = new Table($this->output->section());
        $table->addRow([new TableCell('Quest ' . $questId->quest, ['colspan' => 3, 'style' => new TableCellStyle(['align' => 'center'])])]);
        $table->addRow(new TableSeparator());

        $start = microtime(true);
        $part1Result = (string) $solution->solvePart1($questInput->input1);
        $part1ExecutionTime = microtime(true) - $start;

        $table->addRow(['Part 1', str_pad($part1Result, 100, ' '), number_format($part1ExecutionTime, 5, '.', '').'s']);

        if (null !== $questInput->input2) {
            $start = microtime(true);
            $part2Result = (string)$solution->solvePart2($questInput->input2);
            $part2ExecutionTime = microtime(true) - $start;

            $table->addRow(
                ['Part 2', str_pad($part2Result, 100, ' '), number_format($part2ExecutionTime, 5, '.', '') . 's']
            );
        }

        if (null !== $questInput->input3) {
            $start = microtime(true);
            $part3Result = (string) $solution->solvePart3($questInput->input3);
            $part3ExecutionTime = microtime(true) - $start;

            $table->addRow(['Part 3', str_pad($part3Result, 100, ' '), number_format($part3ExecutionTime, 5, '.', '').'s']);
        }

        $table->render();
    }

    private function getSolutionForQuest(QuestId $questId): QuestInterface
    {
        $quest = $this->eventQuestRegistry->getQuestInYear($questId->event, $questId->quest);

        if (!$quest) {
            throw new \RuntimeException('Could not find solution for event ' . $questId->event . ' quest ' . $questId->quest);
        }

        return $quest;
    }

    private function getDataForQuest(QuestId $questId): QuestData
    {
        return $this->dataResolver->getDataForEventAndQuest($questId->event, $questId->quest);
    }
}
