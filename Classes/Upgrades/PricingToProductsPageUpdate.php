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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Upgrades\ChattyInterface;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;

/**
 * The demo "Pricing" page made visitors believe the t3wtk packages cost money. It is now a
 * "Products" page (free toolkit, demo bundle, project request) — see Definition.php.
 *
 * New installs get the new page from Definition.php; this wizard brings existing demo sites
 * in line. Records are matched by their original demo content, not by uid, and anything an
 * editor already changed is left alone.
 *
 * upgrade:run boots with caches disabled, so run `typo3 cache:flush` afterwards.
 */
#[UpgradeWizard('connT3wtkDemositePricingToProductsPage')]
final class PricingToProductsPageUpdate implements UpgradeWizardInterface, ChattyInterface
{
    private const OLD_SLUG = '/pricing';
    private const NEW_SLUG = '/products';

    private const FAQ_TITLE = 'Does t3wtk cost anything?';

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
        return 'EXT:conn_t3wtk_demosite: Turn the demo "Pricing" page into a "Products" page';
    }

    public function getDescription(): string
    {
        return 'Renames the demo page "Pricing" (/pricing) to "Products" (/products) and replaces the '
            . 'example prices with the free toolkit, the demo bundle and a project request, so visitors no '
            . 'longer think the packages cost money.';
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    public function updateNecessary(): bool
    {
        return $this->findPricingPages() !== [];
    }

    public function executeUpdate(): bool
    {
        foreach ($this->findPricingPages() as $page) {
            $pageUid = (int)$page['uid'];
            $this->log(sprintf('Page %d "%s"', $pageUid, $page['title']));

            $contactLink = $this->updateContent($pageUid);
            $this->updatePage($pageUid);
            $this->log('  done' . ($contactLink === '' ? ' (no contact link found, kept existing card links)' : ''));
        }

        $this->log('Run "typo3 cache:flush" to see the changes in the frontend.');

        return true;
    }

    /**
     * Default-language pages with the old slug that still hold the demo pricing table.
     *
     * @return list<array<string, mixed>>
     */
    private function findPricingPages(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $pages = $queryBuilder
            ->select('uid', 'title')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter(self::OLD_SLUG)),
                $queryBuilder->expr()->eq('sys_language_uid', 0),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return array_values(array_filter(
            $pages,
            fn(array $page): bool => $this->findContent((int)$page['uid'], ['CType' => 'conn_bs_pricing']) !== []
        ));
    }

    /**
     * Updates the content elements and returns the contact link the old demo cards pointed to.
     */
    private function updateContent(int $pageUid): string
    {
        $now = time();
        $contactLink = '';

        foreach ($this->findContent($pageUid, ['CType' => 'conn_bs_pricing']) as $pricing) {
            $contactLink = $this->updateTiers((int)$pricing['uid'], $now) ?: $contactLink;
            $this->updateRecord('tt_content', ['uid' => (int)$pricing['uid'], 'header' => 'Plans'], ['header' => 'Products', 'tstamp' => $now], 'pricing table header');
        }

        $this->updateRecord('tt_content', ['pid' => $pageUid, 'CType' => 'text', 'subheader' => 'Pricing', 'header' => 'Simple, transparent pricing'], [
            'subheader' => 'Products',
            'header' => 'Free and open source — with help when you need it',
            'bodytext' => '<p class="lead text-center">All t3wtk packages are free under the GPL. Try the demo bundle, or send us a request if you want us to build your website.</p>',
            'tstamp' => $now,
        ], 'intro');

        if ($contactLink !== '') {
            $this->updateRecord('tt_content', ['pid' => $pageUid, 'CType' => 'text', 'header' => 'Not sure which plan is right?'], [
                'header' => 'Ready to try t3wtk?',
                'bodytext' => '<p class="text-center lead">Request the demo bundle or tell us about your project.</p>'
                    . '<p class="text-center"><a href="' . htmlspecialchars($contactLink) . '" class="btn btn-primary btn-lg">Send a request</a></p>',
                'tstamp' => $now,
            ], 'call to action');
        }

        foreach ($this->findContent($pageUid, ['CType' => 'conn_bs_accordion']) as $faq) {
            $this->updateFaq((int)$faq['uid'], $pageUid, $now);
        }

        return $contactLink;
    }

    /**
     * Replaces the Starter/Business/Enterprise demo tiers; returns their contact link.
     */
    private function updateTiers(int $contentUid, int $now): string
    {
        $table = 'tx_conn_t3wtk_elements_domain_model_pricing_tier';
        $tiers = $this->findRecords($table, ['content_element' => $contentUid]);
        $tiersByName = array_column($tiers, null, 'name');
        $contactLink = (string)($tiersByName['Business']['cta_link'] ?? $tiersByName['Starter']['cta_link'] ?? '');

        // Empty amount renders as "Free"
        $newTiers = [
            'Starter' => [
                'name' => 'Toolkit', 'tagline' => 'Base, elements and sitepackage', 'currency' => '', 'amount' => '', 'period' => '',
                'price_note' => 'Open source, GPL-2.0-or-later',
                'features' => "30+ content elements\nLight and dark theme\nSite Sets for TYPO3 LTS 14\nInstall via Composer",
                'cta_label' => 'Get the quickstart', 'cta_link' => 'https://github.com/connetation/t3wtk-quickstart-shell',
                'featured' => 0, 'badge_text' => '',
            ],
            'Business' => [
                'name' => 'Demo bundle', 'tagline' => 'See everything in action', 'currency' => '', 'amount' => '', 'period' => '',
                'price_note' => 'Open source, GPL-2.0-or-later',
                'features' => "Complete demo page tree\nSample content for every element\nSix color themes\nInstalls with one command",
                'cta_label' => 'Request the demo bundle',
                'featured' => 1, 'badge_text' => 'Try it first',
            ],
            'Enterprise' => [
                'name' => 'Project request', 'tagline' => 'Your website, built by Connetation', 'currency' => '', 'amount' => 'On request', 'period' => '',
                'price_note' => '',
                'features' => "Theme and design for your brand\nCustom content elements\nUpdates and maintenance\nEditor training",
                'cta_label' => 'Send a project request',
                'featured' => 0, 'badge_text' => '',
            ],
        ];

        foreach ($newTiers as $oldName => $fields) {
            if (!isset($tiersByName[$oldName])) {
                $this->log(sprintf('  tier "%s" not found (edited?), skipped', $oldName));
                continue;
            }
            // Demo bundle and project request keep the card's existing (contact page) link
            $this->updateRecord($table, ['uid' => (int)$tiersByName[$oldName]['uid']], $fields + ['tstamp' => $now], sprintf('tier "%s" → "%s"', $oldName, $fields['name']));
        }

        return $contactLink;
    }

    private function updateFaq(int $contentUid, int $pageUid, int $now): void
    {
        $table = 'tx_conn_t3wtk_elements_domain_model_accordion_item';

        $this->updateRecord($table, ['content_element' => $contentUid, 'title' => 'What is t3kit?'], [
            'title' => 'What is t3wtk?',
            'body' => '<p>t3wtk is a set of TYPO3 extensions — base, elements and sitepackage — that gives you a complete, themeable website toolkit for TYPO3 LTS 14.</p>',
            'tstamp' => $now,
        ], 'FAQ "What is t3kit?" → "What is t3wtk?"');

        if ($this->findRecords($table, ['content_element' => $contentUid, 'title' => self::FAQ_TITLE]) !== []) {
            return;
        }

        $connection = $this->connectionPool->getConnectionForTable($table);
        $connection->insert($table, [
            'pid' => $pageUid,
            'tstamp' => $now,
            'crdate' => $now,
            'sorting' => 0,
            'content_element' => $contentUid,
            'title' => self::FAQ_TITLE,
            'body' => '<p>No. All t3wtk packages are open source under GPL-2.0-or-later. You only pay if you want us to build, extend or maintain your website.</p>',
        ]);
        $this->connectionPool->getConnectionForTable('tt_content')->executeStatement(
            'UPDATE tt_content SET tx_conn_t3wtk_elements_accordion_items = tx_conn_t3wtk_elements_accordion_items + 1 WHERE uid = ?',
            [$contentUid],
            [Connection::PARAM_INT]
        );
        $this->log(sprintf('  FAQ "%s" added', self::FAQ_TITLE));
    }

    private function updatePage(int $pageUid): void
    {
        $this->connectionPool->getConnectionForTable('pages')->update('pages', [
            'title' => 'Products',
            'slug' => self::NEW_SLUG,
            'description' => 'The t3wtk products: free open-source TYPO3 toolkit, demo bundle to try it out, and project support by Connetation on request.',
            'tstamp' => time(),
        ], ['uid' => $pageUid]);
        $this->log(sprintf('  page renamed, slug %s → %s', self::OLD_SLUG, self::NEW_SLUG));
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
    private function findContent(int $pageUid, array $match): array
    {
        return $this->findRecords('tt_content', ['pid' => $pageUid, 'sys_language_uid' => 0] + $match);
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
