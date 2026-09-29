<?php

declare(strict_types=1);

namespace Connetation\T3kitDemosite\Theme;

/**
 * One conn/t3kit-theme-* site set, as offered by the demo theme switcher —
 * or the default look (setName ''), which is conn/t3kit-base without any theme set.
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
