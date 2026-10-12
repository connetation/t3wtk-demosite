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
