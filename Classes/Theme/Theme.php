<?php

declare(strict_types=1);

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
