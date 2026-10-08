<?php

declare(strict_types=1);

namespace Connetation\T3wtkDemosite\Installer;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Creates the demo page tree, its content and the site configuration from the declarative
 * definition in Configuration/DemoSite/Definition.php — all through DataHandler, so every
 * record is created exactly as if an editor had built it in the backend.
 *
 * Definition format (see Definition.php for the real thing):
 *   page:    ['_key' => 'about', <pages fields>, '_content' => [ce, …], '_subpages' => [page, …],
 *             '_records' => ['<table>' => [[<fields>, '_files' => …], …]]]
 *   content: [<tt_content fields>,
 *             '_files'    => ['<file field>' => ['hero.jpg', ['file' => 'x.jpg', 'alternative' => '…']]],
 *             '_inline'   => ['<inline field>' => [[<child fields>, '_files' => …, '_inline' => …], …]],
 *             '_flexform' => ['<sheet>' => ['<field>' => 'value']],  (flex column detected from the CType's showitem,
 *                                                                 or set '_flexformField' => '<column>')
 *             '_children' => [<container colPos> => [ce, …]]]
 *   Any string value may contain {page:<key>} which is replaced with that page's uid.
 *   'site' is written to config/sites/t3kit-demosite/config.yaml, 'settings' to its settings.yaml.
 */
final class DemoSiteInstaller
{
    public const EXTENSION_KEY = 'conn_t3wtk_demosite';
    public const SITE_IDENTIFIER = 't3kit-demosite';
    private const REGISTRY_NAMESPACE = 'extensionDataImport';
    private const REGISTRY_KEY = self::EXTENSION_KEY . ':demoSiteInstalled';
    /** Same flag under the former extension key (conn_t3kit_demosite, before the t3wtk rename). */
    private const LEGACY_REGISTRY_KEY = 'conn_t3kit_demosite:demoSiteInstalled';
    private const FILE_FOLDER = self::EXTENSION_KEY . '/images/';

    /** @var array<string, int> page key => uid */
    private array $pageUids = [];
    private int $newIdCounter = 0;

    public function __construct(
        private readonly Registry $registry,
        private readonly SiteConfiguration $siteConfiguration,
        private readonly SiteWriter $siteWriter,
        private readonly StorageRepository $storageRepository,
        private readonly FlexFormTools $flexFormTools,
        private readonly TcaSchemaFactory $tcaSchemaFactory,
        private readonly LoggerInterface $logger,
    ) {}

    public function isInstalled(): bool
    {
        return (bool)$this->registry->get(self::REGISTRY_NAMESPACE, self::REGISTRY_KEY)
            || (bool)$this->registry->get(self::REGISTRY_NAMESPACE, self::LEGACY_REGISTRY_KEY);
    }

    /**
     * @return int uid of the new root page
     */
    public function install(): int
    {
        if (!($GLOBALS['BE_USER'] ?? null)?->isAdmin()) {
            throw new \RuntimeException('The demo site can only be installed by an admin backend user.', 1759140001);
        }
        if (isset($this->siteConfiguration->resolveAllExistingSites(false)[self::SITE_IDENTIFIER])) {
            throw new \RuntimeException(
                sprintf('A site with identifier "%s" already exists. Delete it (and its page tree) first.', self::SITE_IDENTIFIER),
                1759140002
            );
        }

        $definition = require ExtensionManagementUtility::extPath(self::EXTENSION_KEY) . 'Configuration/DemoSite/Definition.php';
        $this->pageUids = [];

        $this->createPages($definition['pages']);
        $rootPageUid = $this->pageUids[$definition['pages'][0]['_key']];
        $this->createContent($definition['pages']);
        $this->writeSiteConfiguration($definition['site'], $rootPageUid);
        if (!empty($definition['settings'])) {
            $this->siteWriter->writeSettings(self::SITE_IDENTIFIER, $this->replacePlaceholders($definition['settings']));
        }

        $this->registry->set(self::REGISTRY_NAMESPACE, self::REGISTRY_KEY, 1);
        $this->logger->info('TYPO3 Web Toolkit demo site installed', ['rootPageUid' => $rootPageUid]);
        return $rootPageUid;
    }

    /**
     * All pages in one DataHandler run. Siblings are inserted in reverse order with a positive pid,
     * because DataHandler puts every new record at the top of its parent.
     */
    private function createPages(array $pages): void
    {
        $data = [];
        $keysByNewId = [];
        $collect = function (array $pages, int|string $pid) use (&$collect, &$data, &$keysByNewId): void {
            foreach (array_reverse($pages) as $page) {
                // New pages are hidden by default (pages TCA) — the demo should be visible right away.
                $newId = $this->addRecord($data, 'pages', $pid, ['hidden' => 0] + array_diff_key($page, ['_content' => 1, '_subpages' => 1, '_records' => 1]));
                $keysByNewId[$newId] = $page['_key'];
                $collect($page['_subpages'] ?? [], $newId);
            }
        };
        $collect($pages, 0);

        $dataHandler = $this->process($data);
        foreach ($keysByNewId as $newId => $key) {
            $this->pageUids[$key] = (int)$dataHandler->substNEWwithIDs[$newId];
        }
    }

    /**
     * Content is created level by level: first all top-level elements of every page, then the
     * children of the containers created in the previous pass (they need the container's real uid).
     */
    private function createContent(array $pages): void
    {
        // [pid, colPos, containerParentUid, elements]
        $groups = [];
        $walk = function (array $pages) use (&$walk, &$groups): void {
            foreach ($pages as $page) {
                if (!empty($page['_content'])) {
                    $groups[] = [$this->pageUids[$page['_key']], null, 0, $page['_content']];
                }
                $walk($page['_subpages'] ?? []);
            }
        };
        $walk($pages);

        // Plain records (e.g. persons in a storage folder) go into the first pass.
        $records = [];
        $walkRecords = function (array $pages) use (&$walkRecords, &$records): void {
            foreach ($pages as $page) {
                foreach ($page['_records'] ?? [] as $table => $tableRecords) {
                    foreach (array_reverse($tableRecords) as $record) {
                        $records[] = [$table, $this->pageUids[$page['_key']], $record];
                    }
                }
                $walkRecords($page['_subpages'] ?? []);
            }
        };
        $walkRecords($pages);

        $firstPass = true;
        while ($groups !== [] || $firstPass) {
            $data = [];
            $pendingChildren = [];
            if ($firstPass) {
                foreach ($records as [$table, $pid, $record]) {
                    $this->addRecord($data, $table, $pid, $record);
                }
                $firstPass = false;
            }
            foreach ($groups as [$pid, $colPos, $parentUid, $elements]) {
                foreach (array_reverse($elements) as $element) {
                    $newId = $this->addRecord($data, 'tt_content', $pid, $element, array_filter([
                        'colPos' => $colPos,
                        'tx_container_parent' => $parentUid ?: null,
                    ], static fn($v) => $v !== null));
                    foreach ($element['_children'] ?? [] as $childColPos => $children) {
                        $pendingChildren[] = [$pid, $childColPos, $newId, $children];
                    }
                }
            }
            if ($data === []) {
                break;
            }
            $dataHandler = $this->process($data);
            $groups = [];
            foreach ($pendingChildren as [$pid, $childColPos, $parentNewId, $children]) {
                $groups[] = [$pid, (int)$childColPos, (int)$dataHandler->substNEWwithIDs[$parentNewId], $children];
            }
        }
    }

    /**
     * Adds one record (plus its file references, inline children and flexform) to the datamap.
     */
    private function addRecord(array &$data, string $table, int|string $pid, array $definition, array $extraFields = []): string
    {
        $newId = $this->newId();
        $fields = ['pid' => $pid] + $extraFields + $this->plainFields($definition);

        if (isset($definition['_flexform'])) {
            $flexField = $definition['_flexformField'] ?? $this->getFlexFormField($table, (string)($fields['CType'] ?? ''));
            // Start from the data structure defaults like a record created in the backend —
            // fields missing in a stored flexform are empty, not their default.
            $sheets = array_replace_recursive(
                $this->getFlexFormDefaults($table, $flexField, $fields),
                $this->replacePlaceholders($definition['_flexform'])
            );
            $fields[$flexField] = ['data' => array_map(
                static fn(array $sheet) => ['lDEF' => array_map(static fn($value) => ['vDEF' => $value], $sheet)],
                $sheets
            )];
        }

        foreach ($definition['_files'] ?? [] as $fieldName => $files) {
            $referenceIds = [];
            foreach ($files as $file) {
                $file = is_array($file) ? $file : ['file' => $file];
                $referenceId = $this->newId();
                $data['sys_file_reference'][$referenceId] = [
                    // References of a page live on that page itself.
                    'pid' => $table === 'pages' ? $newId : $pid,
                    'uid_local' => $this->getFile($file['file'])->getUid(),
                    'uid_foreign' => $newId,
                    'tablenames' => $table,
                    'fieldname' => $fieldName,
                ] + array_diff_key($file, ['file' => true]);
                $referenceIds[] = $referenceId;
            }
            $fields[$fieldName] = implode(',', $referenceIds);
        }

        foreach ($definition['_inline'] ?? [] as $fieldName => $children) {
            $childTable = $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['foreign_table'] ?? null;
            if ($childTable === null) {
                throw new \RuntimeException(sprintf('%s.%s is not an inline field.', $table, $fieldName), 1759140003);
            }
            $childIds = [];
            foreach ($children as $child) {
                $childIds[] = $this->addRecord($data, $childTable, $pid, $child);
            }
            $fields[$fieldName] = implode(',', $childIds);
        }

        $data[$table][$newId] = $fields;
        return $newId;
    }

    /**
     * The flex field of a CType: t3wtk elements keep their settings in own columns
     * (e.g. tx_conn_t3wtk_elements_logo_wall_settings), core plugins use pi_flexform.
     */
    private function getFlexFormField(string $table, string $cType): string
    {
        $showItem = $GLOBALS['TCA'][$table]['types'][$cType]['showitem'] ?? '';
        foreach (GeneralUtility::trimExplode(',', $showItem, true) as $item) {
            $column = GeneralUtility::trimExplode(';', $item)[0];
            if (($GLOBALS['TCA'][$table]['columns'][$column]['config']['type'] ?? '') === 'flex') {
                return $column;
            }
        }
        return 'pi_flexform';
    }

    /**
     * @return array<string, array<string, mixed>> sheet => field => default value
     */
    private function getFlexFormDefaults(string $table, string $field, array $row): array
    {
        try {
            $schema = $this->tcaSchemaFactory->get($table);
            $identifier = $this->flexFormTools->getDataStructureIdentifier($GLOBALS['TCA'][$table]['columns'][$field] ?? [], $table, $field, $row, $schema);
            $dataStructure = $this->flexFormTools->parseDataStructureByIdentifier($identifier, $schema);
        } catch (\Throwable $e) {
            // Not fatal: the flexform is then stored with the given values only.
            $this->logger->warning('No flexform defaults for {table}.{field}: {message}', ['table' => $table, 'field' => $field, 'message' => $e->getMessage()]);
            return [];
        }
        $defaults = [];
        foreach ($dataStructure['sheets'] ?? [] as $sheetName => $sheet) {
            foreach ($sheet['ROOT']['el'] ?? [] as $fieldName => $fieldConfig) {
                if (array_key_exists('default', $fieldConfig['config'] ?? [])) {
                    $defaults[$sheetName][$fieldName] = $fieldConfig['config']['default'];
                }
            }
        }
        return $defaults;
    }

    private function writeSiteConfiguration(array $site, int $rootPageUid): void
    {
        $site = array_replace($site, $this->resolveBase($site['baseWithOtherSites'] ?? '/t3kit-demo/'));
        unset($site['baseWithOtherSites']);
        $site['rootPageId'] = $rootPageUid;
        $this->siteWriter->write(self::SITE_IDENTIFIER, $this->replacePlaceholders($site));
    }

    /**
     * Standalone installation: the demo owns "/". Next to existing sites it gets its own path on
     * the first existing site's domain(s) — a relative base would lose against a site whose base
     * has a host, so base and baseVariants are copied with the path appended.
     *
     * @return array{base: string, baseVariants?: list<array{base: string, condition: string}>}
     */
    private function resolveBase(string $path): array
    {
        $existingSites = $this->siteConfiguration->resolveAllExistingSites(false);
        if ($existingSites === []) {
            return ['base' => '/'];
        }
        $reference = $this->siteConfiguration->load(array_key_first($existingSites));
        $withPath = static fn(string $base): string => rtrim($base, '/') . '/' . trim($path, '/') . '/';
        $result = ['base' => $withPath((string)($reference['base'] ?? ''))];
        foreach ($reference['baseVariants'] ?? [] as $variant) {
            $result['baseVariants'][] = ['base' => $withPath((string)$variant['base']), 'condition' => $variant['condition']];
        }
        return $result;
    }

    private function process(array $data): DataHandler
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        // Marks this as an import: among others, the core then doesn't auto-create an
        // "autogenerated-<uid>" site for the new root page (CreateSiteConfiguration hook).
        $dataHandler->isImporting = true;
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
        if ($dataHandler->errorLog !== []) {
            throw new \RuntimeException('Creating demo records failed: ' . implode(' | ', $dataHandler->errorLog), 1759140004);
        }
        return $dataHandler;
    }

    private function getFile(string $fileName): File
    {
        $storage = $this->storageRepository->getDefaultStorage()
            ?? throw new \RuntimeException('No default file storage (fileadmin) found.', 1759140005);
        $file = $storage->getFile(self::FILE_FOLDER . $fileName);
        if (!$file instanceof File) {
            throw new \RuntimeException(sprintf('Demo file "%s" not found in fileadmin/%s.', $fileName, self::FILE_FOLDER), 1759140006);
        }
        return $file;
    }

    /**
     * Record fields without the "_" meta keys, with {page:key} placeholders resolved.
     */
    private function plainFields(array $definition): array
    {
        return $this->replacePlaceholders(array_filter(
            $definition,
            static fn($key) => !str_starts_with((string)$key, '_'),
            ARRAY_FILTER_USE_KEY
        ));
    }

    private function replacePlaceholders(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map($this->replacePlaceholders(...), $value);
        }
        if (!is_string($value) || !str_contains($value, '{page:')) {
            return $value;
        }
        return preg_replace_callback('/\{page:([a-z0-9_-]+)\}/', function (array $match): string {
            return (string)($this->pageUids[$match[1]]
                ?? throw new \RuntimeException(sprintf('Unknown page key "%s" in demo definition.', $match[1]), 1759140007));
        }, $value);
    }

    private function newId(): string
    {
        return 'NEW' . (++$this->newIdCounter);
    }
}
