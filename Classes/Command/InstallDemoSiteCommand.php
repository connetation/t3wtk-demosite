<?php

declare(strict_types=1);

/*
 * This file is part of the Connetation TYPO3 Web Toolkit - T3WTK.
 *
 * Copyright (c) Connetation Web Engineering GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Connetation\T3wtkDemosite\Command;

use Connetation\T3wtkDemosite\Installer\DemoSiteInstaller;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Bootstrap;

#[AsCommand(
    name: 't3wtk:demosite:install',
    description: 'Creates the TYPO3 Web Toolkit demo page tree, content and site configuration.',
    // Former name, kept for existing scripts.
    aliases: ['t3kit:demosite:install'],
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
        $io->success(sprintf('TYPO3 Web Toolkit demo site installed (root page uid %d, site "%s").', $rootPageUid, DemoSiteInstaller::SITE_IDENTIFIER));
        return Command::SUCCESS;
    }
}
