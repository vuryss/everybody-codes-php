<?php

declare(strict_types=1);

namespace App\Commands;

use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsCommand('generate', 'Generates PHP class wrapper for solution to the given quest.')]
class GenerateCommand extends Command
{
    public function __construct(
        private readonly Environment $templating,
        private readonly string $eventsDirectory,
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
                description: 'Which event of Everybody Codes to use.',
            )
            ->addArgument(
                name: 'quest',
                mode: InputArgument::REQUIRED,
                description: 'Quest to generate',
            )
        ;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        [$year, $quest] = $this->resolveEventQuest($input);

        $destinationFile = sprintf('%s/Year%s/Quest%s.php', $this->eventsDirectory, $year, $quest);

        if (file_exists($destinationFile)) {
            $output->writeln(
                sprintf(
                    '<error>Wrapper class for quest %s of %s event already exists at %s</error>',
                    $quest,
                    $year,
                    $destinationFile
                )
            );
            return Command::FAILURE;
        }

        $wrapperClassCode = $this->templating->render(
            'quest-wrapper-class.twig',
            [
                'event' => $year,
                'quest' => $quest,
            ]
        );

        if (!is_dir(dirname($destinationFile))) {
            mkdir(dirname($destinationFile), 0777, true);
        }

        $bytesWritten = file_put_contents(
            $destinationFile,
            $wrapperClassCode
        );

        if (!$bytesWritten) {
            $output->writeln('<error>Could not write event quest wrapper class</error>');
            return Command::FAILURE;
        }

        $output->writeln(
            sprintf(
                '<info>Wrapper class for quest %s of %s event generated in %s</info>',
                $quest,
                $year,
                $destinationFile
            )
        );
        return Command::SUCCESS;
    }

    private function resolveEventQuest(InputInterface $input): array
    {
        $year = (int) $input->getArgument('event');
        $quest = (int) $input->getArgument('quest');

        if ($year < 2024 || $year > (int) date('Y')) {
            throw new LogicException(
                sprintf('Invalid event year given. Allowed values are between 2024 and %s', date('Y'))
            );
        }

        if ($quest < 1 || $quest > 20 || (!is_int($quest) && !ctype_digit($quest))) {
            throw new LogicException('Invalid event quest given. Allowed values are between 1 and 20');
        }

        return [(int) $year, (int) $quest];
    }
}
