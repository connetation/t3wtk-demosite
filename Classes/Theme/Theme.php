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

/**
 * One connetation/t3wtk-theme-* site set, as offered by the demo theme switcher —
 * or the default look (setName ''), which is connetation/t3wtk-base without any theme set.
 */
final readonly class Theme
{
    public function __construct(
        public string $identifier,
        public string $setName,
        public string $label,
        public string $accent,
    ) {}

    public function isDefault(): bool
    {
        return $this->setName === '';
    }
}
