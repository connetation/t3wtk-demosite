<?php

declare(strict_types=1);

return [
    'frontend' => [
        // Right after site resolution, so everything below renders with the switched site.
        'connetation/t3wtk-demosite/theme-switcher' => [
            'target' => \Connetation\T3wtkDemosite\Middleware\ThemeSwitcherMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
                'typo3/cms-frontend/page-resolver',
                'connetation/t3wtk-base/branding-icons',
            ],
        ],
    ],
];
