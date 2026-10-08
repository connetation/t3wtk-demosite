<?php

declare(strict_types=1);

namespace Connetation\T3wtkDemosite\DataProcessing;

use Connetation\T3wtkDemosite\Middleware\ThemeSwitcherMiddleware;
use Connetation\T3wtkDemosite\Theme\ThemeRegistry;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Provides the theme list for the switcher (Templates/ThemeSwitcher/Banner.html):
 * one entry per connetation/t3wtk-theme-* set, each linking to the current URL plus
 * ?t3kit-theme=<identifier> (handled by ThemeSwitcherMiddleware).
 */
final readonly class ThemeSwitcherProcessor implements DataProcessorInterface
{
    public function __construct(
        private ThemeRegistry $themeRegistry,
    ) {}

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $request = $cObj->getRequest();
        $active = $request->getAttribute(ThemeSwitcherMiddleware::ATTRIBUTE);
        $uri = $request->getUri();
        $queryParams = $request->getQueryParams();

        $themes = [];
        // No attribute: switcher disabled or the site has no theme set to swap.
        foreach (is_string($active) ? $this->themeRegistry->getThemes() : [] as $theme) {
            $themes[] = [
                'identifier' => $theme->identifier,
                'label' => $theme->label,
                'accent' => $theme->accent,
                'active' => $theme->identifier === $active,
                'href' => (string)$uri->withQuery(http_build_query(
                    [ThemeSwitcherMiddleware::PARAMETER => $theme->identifier] + $queryParams
                )),
            ];
        }

        $processedData[$cObj->stdWrapValue('as', $processorConfiguration, 'themeSwitcher')] = [
            'themes' => $themes,
            'active' => $active,
            // Entry of the active theme, for the collapsed toggle button
            'current' => current(array_filter($themes, static fn(array $theme): bool => $theme['active'])) ?: null,
        ];

        return $processedData;
    }
}
