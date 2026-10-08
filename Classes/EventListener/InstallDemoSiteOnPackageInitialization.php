<?php

declare(strict_types=1);

namespace Connetation\T3wtkDemosite\EventListener;

use Connetation\T3wtkDemosite\Installer\DemoSiteInstaller;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Package\Event\PackageInitializationEvent;
use TYPO3\CMS\Core\Package\Initialization\ImportExtensionDataOnPackageInitialization;

/**
 * Installs the demo site the first time this extension is set up (`typo3 extension:setup`,
 * TYPO3 installation, or activation in the Extension Manager) — like EXT:introduction.
 * Runs after the core copied Initialisation/Files to fileadmin/conn_t3wtk_demosite/.
 */
final readonly class InstallDemoSiteOnPackageInitialization
{
    public function __construct(
        private DemoSiteInstaller $installer,
        private LoggerInterface $logger,
    ) {}

    #[AsEventListener(after: ImportExtensionDataOnPackageInitialization::class)]
    public function __invoke(PackageInitializationEvent $event): void
    {
        if ($event->getExtensionKey() !== DemoSiteInstaller::EXTENSION_KEY || $this->installer->isInstalled()) {
            return;
        }
        try {
            $rootPageUid = $this->installer->install();
            $event->addStorageEntry(__CLASS__, ['rootPageUid' => $rootPageUid]);
        } catch (\RuntimeException $e) {
            // Never break extension:setup / the installation because of demo content.
            $this->logger->warning('TYPO3 Web Toolkit demo site was not installed: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}
