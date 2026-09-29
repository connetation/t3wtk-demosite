<?php

declare(strict_types=1);

return [
    'frontend' => [
        // Right after site resolution, so everything below renders with the switched site.
        'connetation/t3kit-demosite/theme-switcher' => [
            'target' => \Connetation\T3kitDemosite\Middleware\ThemeSwitcherMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
                'typo3/cms-frontend/page-resolver',
                'connetation/t3kit-base/branding-icons',
            ],
        ],
    ],
];
