<?php

declare(strict_types=1);

namespace Connetation\T3wtkDemosite\EventListener;

use Connetation\T3wtkDemosite\Middleware\ThemeSwitcherMiddleware;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Frontend\Event\BeforePageCacheIdentifierIsHashedEvent;

/**
 * The theme switcher renders the same URL with different site settings —
 * keep one page cache entry per theme instead of serving whichever came first.
 */
#[AsEventListener('conn-t3kit-demosite/theme-page-cache-identifier')]
final class AddThemeToPageCacheIdentifier
{
    public function __invoke(BeforePageCacheIdentifierIsHashedEvent $event): void
    {
        $theme = $event->getRequest()->getAttribute(ThemeSwitcherMiddleware::ATTRIBUTE);
        if (!is_string($theme)) {
            return;
        }
        $parameters = $event->getPageCacheIdentifierParameters();
        $parameters['connDemositeTheme'] = $theme;
        $event->setPageCacheIdentifierParameters($parameters);
    }
}
