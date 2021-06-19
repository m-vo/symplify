<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\SnippetFormatter\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\EasyCodingStandard\Console\Command\AbstractCheckCommand;
use Symplify\EasyCodingStandard\SnippetFormatter\Application\SnippetFormatterApplication;
use Symplify\EasyCodingStandard\SnippetFormatter\ValueObject\SnippetKind;
use Symplify\EasyCodingStandard\SnippetFormatter\ValueObject\SnippetPattern;
use Symplify\PackageBuilder\Console\ShellCode;

final class CheckMarkdownCommand extends AbstractCheckCommand
{
    public function __construct(
        private SnippetFormatterApplication $snippetFormatterApplication
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Format Markdown PHP code');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $this->loadedCheckersGuard->areSomeCheckerRegistered()) {
            $this->loadedCheckersGuard->report();
            return ShellCode::ERROR;
        }

        $configuration = $this->configurationFactory->createFromInput($input);
        $phpFileInfos = $this->smartFinder->find($configuration->getSources(), '*.php', ['Fixture']);

        return $this->snippetFormatterApplication->processFileInfosWithSnippetPattern(
            $configuration,
            $phpFileInfos,
            SnippetPattern::MARKDOWN_PHP_SNIPPET_REGEX,
            SnippetKind::MARKDOWN
        );
    }
}
