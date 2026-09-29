<?php

declare(strict_types=1);

namespace Connetation\T3kitDemosite\Theme;

use TYPO3\CMS\Core\Settings\Settings;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;

/**
 * Gives every theme a readable logo: for each logo slot (navbar/footer ×
 * light/dark mode) the demo logo for dark or light backgrounds is picked from
 * the brightness of that slot's background color. Slots a site sets itself
 * (non-empty) are left alone.
 */
final class ThemeLogos
{
    // conn_t3kit_base's built-in backgrounds (Scss/theme/_light.scss, _dark.scss)
    private const DEFAULT_BACKGROUND = ['light' => '#ffffff', 'dark' => '#12122a'];

    private const SLOTS = [
        'branding.logo' => ['theme.light.navBg', 'light'],
        'branding.logoDark' => ['theme.dark.navBg', 'dark'],
        'footer.logo' => ['theme.light.footerBg', 'light'],
        'footer.logoDark' => ['theme.dark.footerBg', 'dark'],
    ];

    public static function isEnabled(SiteSettings $settings): bool
    {
        return (string)$settings->get('demosite.logo.onLight', '') !== ''
            && (string)$settings->get('demosite.logo.onDark', '') !== '';
    }

    public static function apply(SiteSettings $settings): SiteSettings
    {
        if (!self::isEnabled($settings)) {
            return $settings;
        }

        $logos = [];
        foreach (self::SLOTS as $slot => [$backgroundSetting, $mode]) {
            if ((string)$settings->get($slot, '') !== '') {
                continue;
            }
            $background = (string)$settings->get($backgroundSetting, '') ?: self::DEFAULT_BACKGROUND[$mode];
            $isDark = self::isDark($background) ?? $mode === 'dark';
            $logos[$slot] = (string)$settings->get($isDark ? 'demosite.logo.onDark' : 'demosite.logo.onLight');
        }

        return $logos === [] ? $settings : SiteSettings::create(new Settings(array_replace($settings->getMap(), $logos)));
    }

    /** Relative luminance below 0.4 counts as dark; null for colors that can't be parsed. */
    private static function isDark(string $color): ?bool
    {
        $color = strtolower(trim($color));
        if (preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/', $color, $match)) {
            $hex = strlen($match[1]) <= 4
                ? implode('', array_map(static fn(string $c): string => $c . $c, str_split(substr($match[1], 0, 3))))
                : substr($match[1], 0, 6);
            $rgb = array_map('hexdec', str_split($hex, 2));
        } elseif (preg_match('/^rgba?\(\s*(\d+)[\s,]+(\d+)[\s,]+(\d+)/', $color, $match)) {
            $rgb = [(int)$match[1], (int)$match[2], (int)$match[3]];
        } else {
            return null;
        }

        $channels = array_map(static function (int|float $value): float {
            $value /= 255;
            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2] < 0.4;
    }
}
