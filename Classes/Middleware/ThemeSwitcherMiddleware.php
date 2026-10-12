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

namespace Connetation\T3wtkDemosite\Middleware;

use Connetation\T3wtkDemosite\Theme\Theme;
use Connetation\T3wtkDemosite\Theme\ThemeLogos;
use Connetation\T3wtkDemosite\Theme\ThemeRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteSettingsFactory;

/**
 * Lets visitors of the demo site preview every connetation/t3wtk-theme-* set.
 *
 * ?t3kit-theme=<identifier> stores the choice in a cookie and redirects to the
 * same URL without the parameter. On every request the chosen theme set then
 * replaces the site's configured theme set (or is added / removed for the
 * default look), and the site settings are composed anew, so colors, navbar,
 * footer and toggle position all follow the theme.
 * The switcher is only active when demosite.themeSwitcher.enabled is set; the
 * demo logos (ThemeLogos, demosite.logo.*) are filled in either way.
 */
final readonly class ThemeSwitcherMiddleware implements MiddlewareInterface
{
    public const PARAMETER = 't3kit-theme';
    public const COOKIE = 't3kit_theme';
    /** Request attribute holding the identifier of the theme this request renders with. */
    public const ATTRIBUTE = 'conn.demosite.theme';

    public function __construct(
        private ThemeRegistry $themeRegistry,
        private SiteSettingsFactory $siteSettingsFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return $handler->handle($request);
        }
        $switcherEnabled = (bool)$site->getSettings()->get('demosite.themeSwitcher.enabled', false);
        if (!$switcherEnabled && !ThemeLogos::isEnabled($site->getSettings())) {
            return $handler->handle($request);
        }
        $configuredTheme = $this->themeRegistry->findInSets($site->getSets());

        $theme = $configuredTheme;
        if ($switcherEnabled) {
            $queryParams = $request->getQueryParams();
            if (array_key_exists(self::PARAMETER, $queryParams)) {
                return $this->storeChoice($request, $site, $configuredTheme, (string)$queryParams[self::PARAMETER]);
            }
            $theme = $this->themeRegistry->getTheme((string)($request->getCookieParams()[self::COOKIE] ?? '')) ?? $configuredTheme;
        }

        $request = $request->withAttribute('site', $this->createSite($site, $configuredTheme, $theme));
        if ($switcherEnabled) {
            // Only set while the switcher is active: ThemeSwitcherProcessor lists no themes without it.
            $request = $request->withAttribute(self::ATTRIBUTE, $theme->identifier);
        }

        return $handler->handle($request);
    }

    /** The site as it renders with $to instead of its configured theme, with the demo logos filled in. */
    private function createSite(Site $site, Theme $from, Theme $to): Site
    {
        if ($to->setName === $from->setName) {
            $sets = $site->getSets();
            $settings = $site->getSettings();
        } else {
            $sets = array_values(array_filter(
                $site->getSets(),
                static fn(string $set): bool => $from->isDefault() || $set !== $from->setName
            ));
            if (!$to->isDefault()) {
                // Take the configured theme's place in the list, or go last.
                $position = $from->isDefault() ? false : array_search($from->setName, $site->getSets(), true);
                array_splice($sets, $position === false ? count($sets) : (int)$position, 0, [$to->setName]);
            }
            // Same order as the core: set defaults first, the site's own settings.yaml on top.
            $settings = $this->siteSettingsFactory->composeSettings(
                $this->siteSettingsFactory->loadLocalSettings($site->getIdentifier()) ?? ($site->getRawConfiguration()['settings'] ?? []),
                $sets
            );
        }

        $configuration = $site->getRawConfiguration();
        $configuration['dependencies'] = $sets;

        return new Site(
            $site->getIdentifier(),
            $site->getRootPageId(),
            $configuration,
            ThemeLogos::apply($settings),
            $site->getTypoScript(),
            $site->getTSconfig()
        );
    }

    /** Unknown identifiers or the site's own theme reset the choice (cookie removed). */
    private function storeChoice(ServerRequestInterface $request, Site $site, Theme $configuredTheme, string $identifier): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        unset($queryParams[self::PARAMETER]);
        $response = new RedirectResponse(
            $request->getUri()->withQuery(http_build_query($queryParams)),
            303,
            ['Cache-Control' => 'no-store']
        );

        $theme = $this->themeRegistry->getTheme($identifier);
        $keep = $theme !== null && $theme->setName !== $configuredTheme->setName;
        $normalizedParams = $request->getAttribute('normalizedParams');
        $cookie = Cookie::create(
            self::COOKIE,
            $keep ? $theme->identifier : '',
            $keep ? time() + 30 * 86400 : 1,
            rtrim($site->getBase()->getPath(), '/') . '/',
            null,
            $normalizedParams instanceof NormalizedParams && $normalizedParams->isHttps(),
            true,
            false,
            Cookie::SAMESITE_LAX
        );

        return $response->withAddedHeader('Set-Cookie', (string)$cookie);
    }
}
