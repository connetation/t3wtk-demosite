<?php

declare(strict_types=1);

namespace Connetation\T3kitDemosite\Theme;

use TYPO3\CMS\Core\Site\Set\SetDefinition;
use TYPO3\CMS\Core\Site\Set\SetRegistry;

/**
 * Discovers every installed theme set by name: any site set called
 * conn/t3kit-theme-<identifier> is a theme, no registration needed.
 * Additionally offers "default": conn/t3kit-base's own settings, no theme set.
 */
final readonly class ThemeRegistry
{
    public const SET_PREFIX = 'conn/t3kit-theme-';
    public const DEFAULT_IDENTIFIER = 'default';
    // conn/t3kit-base's built-in accent ($conn-accent in _variables.scss)
    private const DEFAULT_ACCENT = '#6366f1';

    public function __construct(
        private SetRegistry $setRegistry,
    ) {}

    /** @return array<string, Theme> keyed by identifier: default first, then sorted by label */
    public function getThemes(): array
    {
        $themes = [];
        foreach ($this->setRegistry->getAllSets() as $set) {
            if (!$set->hidden && str_starts_with($set->name, self::SET_PREFIX) && $set->name !== self::SET_PREFIX . self::DEFAULT_IDENTIFIER) {
                $theme = $this->createTheme($set);
                $themes[$theme->identifier] = $theme;
            }
        }
        uasort($themes, static fn(Theme $a, Theme $b): int => strcasecmp($a->label, $b->label));

        return [self::DEFAULT_IDENTIFIER => $this->createDefaultTheme()] + $themes;
    }

    public function getTheme(string $identifier): ?Theme
    {
        return $this->getThemes()[$identifier] ?? null;
    }

    /** Returns the theme among the given set names (a site's dependencies); the default theme if none. */
    public function findInSets(array $setNames): Theme
    {
        foreach ($setNames as $setName) {
            if (is_string($setName) && str_starts_with($setName, self::SET_PREFIX)) {
                $theme = $this->getTheme(substr($setName, strlen(self::SET_PREFIX)));
                if ($theme !== null && !$theme->isDefault()) {
                    return $theme;
                }
            }
        }

        return $this->createDefaultTheme();
    }

    private function createDefaultTheme(): Theme
    {
        return new Theme(
            identifier: self::DEFAULT_IDENTIFIER,
            setName: '',
            label: 'Default',
            accent: self::DEFAULT_ACCENT,
        );
    }

    private function createTheme(SetDefinition $set): Theme
    {
        // Set settings.yaml files use flat dotted keys; accept a nested tree as well.
        $accent = $set->settings['theme.light.accent']
            ?? $set->settings['theme']['light']['accent']
            ?? '';

        return new Theme(
            identifier: substr($set->name, strlen(self::SET_PREFIX)),
            setName: $set->name,
            label: preg_replace('/^Theme:\s*/i', '', $set->label) ?: $set->name,
            accent: is_string($accent) ? $accent : '',
        );
    }
}
