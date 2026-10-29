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

namespace Connetation\T3wtkDemosite\Upgrades;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Upgrades\ChattyInterface;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;

/**
 * The demo start page did not say clearly enough that it is a demo, and the "Products" buttons
 * led to the demo contact form, which does not send anything. The start page now states that
 * its content is sample content, and the product buttons open a mail to office@connetation.at
 * — see Definition.php.
 *
 * New installs get this from Definition.php; this wizard brings existing demo sites in line.
 * Records are matched by their original demo content, not by uid, and anything an editor
 * already changed is left alone.
 *
 * Runs after connT3wtkDemositePricingToProductsPage, which creates the "Products" page.
 * upgrade:run boots with caches disabled, so run `typo3 cache:flush` afterwards.
 */
#[UpgradeWizard('connT3wtkDemositeDemoNoticeAndMailLinks')]
final class DemoNoticeAndMailLinksUpdate implements UpgradeWizardInterface, ChattyInterface
{
    private const PRODUCTS_SLUG = '/products';

    private const OLD_INTRO_HEADER = 'Everything you need for a modern TYPO3 website';
    private const OLD_SLIDE_TITLE = 'Build beautiful TYPO3 websites';

    private const EMAIL = 'office@connetation.at';
    private const SUBJECT = 't3wtk contact request';

    /** Tier name => button label */
    private const TIERS = [
        'Toolkit' => 'Get the quickstart',
        'Demo bundle' => 'Request the demo bundle',
        'Project request' => 'Send a project request',
    ];

    private ?OutputInterface $output = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getTitle(): string
    {
        return 'EXT:conn_t3wtk_demosite: Mark the start page as a demo and let product buttons open a mail';
    }

    public function getDescription(): string
    {
        return 'Says on the demo start page that its content is sample content, and points the buttons on the '
            . '"Products" page to a mail to ' . self::EMAIL . ' instead of the demo contact form, which does not send anything.';
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    public function updateNecessary(): bool
    {
        foreach ($this->findPages(['is_siteroot' => 1]) as $page) {
            if ($this->findRecords('tt_content', ['pid' => (int)$page['uid'], 'sys_language_uid' => 0, 'CType' => 'text', 'header' => self::OLD_INTRO_HEADER]) !== []) {
                return true;
            }
        }
        foreach ($this->findPages(['slug' => self::PRODUCTS_SLUG]) as $page) {
            foreach ($this->findTiers((int)$page['uid']) as $tier) {
                if ((self::TIERS[$tier['name']] ?? null) === $tier['cta_label'] && $tier['cta_link'] !== $this->mailto($tier['name'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function executeUpdate(): bool
    {
        $now = time();

        foreach ($this->findPages(['is_siteroot' => 1]) as $page) {
            $this->log(sprintf('Start page %d "%s"', $page['uid'], $page['title']));
            $this->updateStartPage((int)$page['uid'], $now);
        }
        foreach ($this->findPages(['slug' => self::PRODUCTS_SLUG]) as $page) {
            $this->log(sprintf('Products page %d "%s"', $page['uid'], $page['title']));
            $this->updateProductsPage((int)$page['uid'], $now);
        }

        $this->log('Run "typo3 cache:flush" to see the changes in the frontend.');

        return true;
    }

    private function updateStartPage(int $pageUid, int $now): void
    {
        $this->updateRecord('tt_content', ['pid' => $pageUid, 'sys_language_uid' => 0, 'CType' => 'text', 'header' => self::OLD_INTRO_HEADER], [
            'subheader' => 'Demo site',
            'header' => 'This website is a demo',
            'bodytext' => '<p class="lead text-center">It shows what the TYPO3 Web Toolkit (t3wtk) by Connetation can do. '
                . 'Every page uses real content elements from connetation/t3wtk-elements, but the companies, projects, '
                . 'testimonials and figures are sample content. Want t3wtk for your project? Write to '
                . '<a href="' . htmlspecialchars($this->mailto()) . '">' . self::EMAIL . '</a>.</p>',
            'tstamp' => $now,
        ], 'intro');

        $this->updateRecord('tx_conn_t3wtk_elements_domain_model_slide_item', ['pid' => $pageUid, 'title' => self::OLD_SLIDE_TITLE], [
            'title' => 'This is the t3wtk demo site',
            'subtitle' => 'Sample content showing the Connetation TYPO3 Web Toolkit for TYPO3 LTS 14.',
            'tstamp' => $now,
        ], 'hero slide');
    }

    private function updateProductsPage(int $pageUid, int $now): void
    {
        foreach ($this->findTiers($pageUid) as $tier) {
            $label = self::TIERS[$tier['name']] ?? null;
            if ($label === null) {
                continue;
            }
            $this->updateRecord('tx_conn_t3wtk_elements_domain_model_pricing_tier', ['uid' => (int)$tier['uid'], 'cta_label' => $label], [
                'cta_link' => $this->mailto($tier['name']),
                'tstamp' => $now,
            ], sprintf('tier "%s" button', $tier['name']));
        }

        $this->updateRecord('tt_content', ['pid' => $pageUid, 'sys_language_uid' => 0, 'CType' => 'text', 'header' => 'Ready to try t3wtk?'], [
            'bodytext' => '<p class="text-center lead">Request the demo bundle or tell us about your project.</p>'
                . '<p class="text-center"><a href="' . htmlspecialchars($this->mailto()) . '" class="btn btn-primary btn-lg">Send a request</a></p>',
            'tstamp' => $now,
        ], 'call to action');
    }

    /** Mail link; the subject names the product ($topic) when one is given. */
    private function mailto(string $topic = ''): string
    {
        return 'mailto:' . self::EMAIL . '?subject=' . rawurlencode(self::SUBJECT . ($topic !== '' ? ': ' . $topic : ''));
    }

    /**
     * @param array<string, int|string> $match
     * @return list<array<string, mixed>>
     */
    private function findPages(array $match): array
    {
        return $this->findRecords('pages', ['sys_language_uid' => 0] + $match);
    }

    /**
     * Tiers of the pricing elements on a page.
     *
     * @return list<array<string, mixed>>
     */
    private function findTiers(int $pageUid): array
    {
        $tiers = [];
        foreach ($this->findRecords('tt_content', ['pid' => $pageUid, 'sys_language_uid' => 0, 'CType' => 'conn_bs_pricing']) as $pricing) {
            array_push($tiers, ...$this->findRecords('tx_conn_t3wtk_elements_domain_model_pricing_tier', ['content_element' => (int)$pricing['uid']]));
        }

        return $tiers;
    }

    /**
     * @param array<string, int|string> $match
     * @param array<string, int|string> $fields
     */
    private function updateRecord(string $table, array $match, array $fields, string $label): void
    {
        $affected = $this->connectionPool->getConnectionForTable($table)->update($table, $fields, $match + ['deleted' => 0]);
        $this->log(sprintf('  %s: %s', $label, $affected > 0 ? 'updated' : 'not found (edited?), skipped'));
    }

    /**
     * @param array<string, int|string> $match
     * @return list<array<string, mixed>>
     */
    private function findRecords(string $table, array $match): array
    {
        return $this->connectionPool->getConnectionForTable($table)->select(['*'], $table, $match + ['deleted' => 0])->fetchAllAssociative();
    }

    private function log(string $message): void
    {
        $this->output?->writeln($message);
    }
}
