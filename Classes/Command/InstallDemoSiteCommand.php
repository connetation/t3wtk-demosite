<?php

declare(strict_types=1);

namespace Connetation\T3kitDemosite\Command;

use Connetation\T3kitDemosite\Installer\DemoSiteInstaller;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Bootstrap;

#[AsCommand(
    name: 't3kit:demosite:install',
    description: 'Creates the t3kit demo page tree, content and site configuration.',
)]
final class InstallDemoSiteCommand extends Command
{
    public function __construct(
        private readonly DemoSiteInstaller $installer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Install again even if it was installed before (the "t3kit-demosite" site must not exist).'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if ($this->installer->isInstalled() && !$input->getOption('force')) {
            $io->note('The demo site was already installed. Use --force to install it again.');
            return Command::SUCCESS;
        }
        Bootstrap::initializeBackendAuthentication();
        try {
            $rootPageUid = $this->installer->install();
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
        $io->success(sprintf('t3kit demo site installed (root page uid %d, site "%s").', $rootPageUid, DemoSiteInstaller::SITE_IDENTIFIER));
        return Command::SUCCESS;
    }
}
