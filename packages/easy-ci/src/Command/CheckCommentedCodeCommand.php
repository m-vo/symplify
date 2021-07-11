<?php

declare(strict_types=1);

namespace Symplify\EasyCI\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\EasyCI\ValueObject\Option;
use Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use Symplify\PackageBuilder\Console\ShellCode;

final class CheckCommentedCodeCommand extends AbstractSymplifyCommand
{
    /**
     * @var int
     */
    private const DEFAULT_LINE_LIMIT = 5;

    protected function configure(): void
    {
        $this->addArgument(
            Option::SOURCES,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'One or more paths to check'
        );
        $this->setDescription('Checks code for commented snippets');

        $this->addOption(
            Option::LINE_LIMIT,
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_OPTIONAL,
            'Amount of allowed comment lines in a row',
            self::DEFAULT_LINE_LIMIT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sources = (array) $input->getArgument(Option::SOURCES);
        $phpFileInfos = $this->smartFinder->find($sources, '*.php');

        $message = sprintf('Analysing %d *.php files', count($phpFileInfos));
        $this->symfonyStyle->note($message);

        foreach ($phpFileInfos as $phpFileInfo) {
            $fileLines = explode(PHP_EOL, $phpFileInfo->getContents());

            $commentLineCount = 0;
            foreach ($fileLines as $key => $fileLine) {
                $isCommentLine = str_starts_with(trim($fileLine), '//');
                if ($isCommentLine) {
                    ++$commentLineCount;
                } else {
                    // crossed the treshold?
                    if ($commentLineCount >= self::DEFAULT_LINE_LIMIT) {
                        $errorMessage = sprintf('To many comments in %d', $key);
                        $this->symfonyStyle->error($errorMessage);
                    }

                    // reset counter
                    $commentLineCount = 0;
                }
            }
        }

        $this->symfonyStyle->success('No errors found');
        return ShellCode::SUCCESS;
    }
}
