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

namespace Connetation\T3wtkDemosite\Theme;

use TYPO3\CMS\Core\Site\Set\SetDefinition;
use TYPO3\CMS\Core\Site\Set\SetRegistry;

/**
 * Discovers every installed theme set by name: any site set called
 * connetation/t3wtk-theme-<identifier> is a theme, no registration needed.
 * Additionally offers "default": connetation/t3wtk-base's own settings, no theme set.
 */
final readonly class ThemeRegistry
{
    public const SET_PREFIX = 'connetation/t3wtk-theme-';
    public const DEFAULT_IDENTIFIER = 'default';
    // connetation/t3wtk-base's built-in accent ($conn-accent in _variables.scss)
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
