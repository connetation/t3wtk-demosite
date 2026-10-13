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

/**
 * t3wtk demo site: page tree, content, site configuration and site settings.
 * Read by Connetation\T3wtkDemosite\Installer\DemoSiteInstaller — see its docblock for the format.
 *
 * Images live in Initialisation/Files/images/ (copied to fileadmin/conn_t3wtk_demosite/images/
 * on extension setup) and are referenced by file name only.
 */

// ── Helpers ────────────────────────────────────────────────────────────────

/** RTE markup from paragraphs. */
$p = static fn(string ...$paragraphs): string => '<p>' . implode('</p><p>', $paragraphs) . '</p>';

/** Bullet list. */
$ul = static fn(string ...$items): string => '<ul><li>' . implode('</li><li>', $items) . '</li></ul>';

/** Button link for RTE text. */
$button = static fn(string $label, string $pageKey, string $style = 'btn-primary'): string
    => '<a href="t3://page?uid={page:' . $pageKey . '}" class="btn ' . $style . '">' . $label . '</a>';

/** Page intro: eyebrow + big heading + lead text. */
$intro = static fn(string $eyebrow, string $header, string $text, string $layout = '1', string $position = 'center'): array => [
    'CType' => 'text',
    'header' => $header,
    'header_layout' => $layout,
    'header_position' => $position,
    'subheader' => $eyebrow,
    'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
    'bodytext' => '<p class="lead' . ($position === 'center' ? ' text-center' : '') . '">' . $text . '</p>',
    'tx_conn_t3wtk_elements_text_animation' => 'words',
    'space_after_class' => 'medium',
];

/** Section header text (h2) above an element. */
$section = static fn(string $eyebrow, string $header, string $text = ''): array => array_replace([
    'CType' => 'header',
    'header' => $header,
    'header_layout' => '2',
    'header_position' => 'center',
    'subheader' => $eyebrow,
    'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
    'space_before_class' => 'large',
    'space_after_class' => 'small',
], $text !== '' ? ['CType' => 'text', 'bodytext' => '<p class="text-center">' . $text . '</p>'] : []);

/*
 * Band colors are theme-neutral (slate / translucent gray): color fields only store #rrggbbaa,
 * so they can't follow the theme Set. Accents inside the bands (buttons, icons, eyebrows) do.
 */

/**
 * Dark color band with light text (the text color only applies to color backgrounds).
 * frame_class "none" + a background = full-bleed background, content stays in the container.
 */
$darkBand = static fn(string $color = '#111827'): array => [
    'frame_class' => 'none',
    'tx_conn_t3wtk_base_bg_type' => 'color',
    'tx_conn_t3wtk_base_bg_color' => $color,
    'tx_conn_t3wtk_base_bg_text_color' => '#f8fafc',
    'tx_conn_t3wtk_base_bg_padding_before' => 'extra-large',
    'tx_conn_t3wtk_base_bg_padding_after' => 'extra-large',
    'space_before_class' => 'extra-large',
];

/** Parallax image band — for elements that bring their own readable surfaces (cards, quotes). */
$imageBand = static fn(string $image): array => [
    'frame_class' => 'none',
    'tx_conn_t3wtk_base_bg_type' => 'image',
    'tx_conn_t3wtk_base_bg_mode' => 'parallax',
    'tx_conn_t3wtk_base_bg_padding_before' => 'extra-large',
    'tx_conn_t3wtk_base_bg_padding_after' => 'extra-large',
    'space_before_class' => 'extra-large',
    '_files' => ['tx_conn_t3wtk_base_bg_image' => [$image]],
];

/** Soft tinted band. */
$softBand = static fn(string $color = '#8080801a'): array => [
    'frame_class' => 'none',
    'tx_conn_t3wtk_base_bg_type' => 'color',
    'tx_conn_t3wtk_base_bg_color' => $color,
    'tx_conn_t3wtk_base_bg_padding_before' => 'large',
    'tx_conn_t3wtk_base_bg_padding_after' => 'large',
    'space_before_class' => 'large',
];

/** Call-to-action band, used at the bottom of most pages. */
$cta = static fn(string $header, string $text, string $label = 'Get in touch', string $target = 'contact'): array => [
    'CType' => 'text',
    'header' => $header,
    'header_layout' => '2',
    'header_position' => 'center',
    'bodytext' => '<p class="text-center lead">' . $text . '</p><p class="text-center">' . $button($label, $target, 'btn-primary btn-lg') . '</p>',
] + $darkBand();

/** Feature item (bootstrap icon). Link labels must describe the target — "Learn more" alone fails link-text audits. */
$feature = static fn(string $icon, string $title, string $body, string $linkPage = '', string $linkLabel = ''): array => [
    'icon_library' => 'bootstrap-icons',
    'icon' => $icon,
    'title' => $title,
    'body' => '<p>' . $body . '</p>',
] + ($linkPage !== '' ? ['link' => 't3://page?uid={page:' . $linkPage . '}', 'link_label' => $linkLabel ?: 'More about ' . $title] : []);

/** Slide for the hero/card/thumbs sliders. */
$slide = static fn(string $image, string $title, string $subtitle, string $linkPage = '', string $linkLabel = 'Discover more', array $extra = []): array => [
    'title' => $title,
    'subtitle' => $subtitle,
    '_files' => ['image' => [$image]],
] + ($linkPage !== '' ? ['link' => 't3://page?uid={page:' . $linkPage . '}', 'link_label' => $linkLabel] : []) + $extra;

/** Card for the Card Group. */
$card = static fn(string $image, string $title, string $body, string $linkPage = '', string $badge = ''): array => [
    'title' => $title,
    'body' => '<p>' . $body . '</p>',
    'badge' => $badge,
    '_files' => ['image' => [$image]],
] + ($linkPage !== '' ? ['link' => 't3://page?uid={page:' . $linkPage . '}', 'link_label' => 'More about ' . $title] : []);

/** Text Icon element. */
$textIcon = static fn(string $icon, string $header, string $text, string $shape = 'circle', string $position = 'top'): array => [
    'CType' => 'conn_text_icon',
    'header' => $header,
    'header_layout' => '3',
    'bodytext' => '<p>' . $text . '</p>',
    'tx_conn_t3wtk_elements_text_icon_library' => 'bootstrap-icons',
    'tx_conn_t3wtk_elements_text_icon_name' => $icon,
    'tx_conn_t3wtk_elements_text_icon_position' => $position,
    'tx_conn_t3wtk_elements_text_icon_shape' => $shape,
    'tx_conn_t3wtk_elements_text_icon_size' => 'large',
];

/** Page with card data for the Subpage Cards element. */
$showcasePage = static fn(string $key, string $title, string $teaser, string $image, array $content): array => [
    '_key' => $key,
    'title' => $title,
    'description' => $teaser,
    'abstract' => $teaser,
    'tx_conn_t3wtk_elements_card_teaser' => '<p>' . $teaser . '</p>',
    '_files' => ['tx_conn_t3wtk_elements_card_media' => [$image]],
    '_content' => $content,
];

$lorem = 'Every element is configurable in the backend — spacing, backgrounds, colors and animations — without touching a single line of code.';

// ── Reusable blocks ────────────────────────────────────────────────────────

$stats = [
    'CType' => 'conn_stats',
    'header' => 'Numbers that speak for themselves',
    'header_layout' => '2',
    'header_position' => 'center',
    '_inline' => ['tx_conn_t3wtk_elements_stats_items' => [
        ['value' => '30', 'suffix' => '+', 'label' => 'Content elements', 'description' => 'Ready to use, no coding'],
        ['value' => '99', 'suffix' => '%', 'label' => 'Lighthouse score', 'description' => 'Performance out of the box'],
        ['value' => '3', 'label' => 'Icon libraries', 'description' => 'Bootstrap, Font Awesome, Ionicons'],
        ['value' => '14', 'prefix' => 'v', 'label' => 'TYPO3 LTS', 'description' => 'Built for the latest release'],
    ]],
    '_flexform' => ['sDEF' => ['settings.columns' => '4', 'settings.layout' => 'row', 'settings.showDivider' => '1', 'settings.countup' => '1', 'settings.accentBar' => '0'], 'colors' => [
        'settings.labelColor' => '#f8fafc',
        'settings.descriptionColor' => '#cbd5e1',
        'settings.dividerColor' => '#ffffff33',
    ]],
] + $darkBand();

$testimonials = [
    'CType' => 'conn_testimonials',
    'header' => 'What our clients say',
    'header_layout' => '2',
    'header_position' => 'center',
    'subheader' => 'Testimonials',
    'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
    'space_before_class' => 'extra-large',
    '_inline' => ['tx_conn_t3wtk_elements_testimonials_items' => [
        ['quote' => '<p>We relaunched three websites in the time it used to take us to build one. Editors love how easy the elements are to use.</p>', 'rating' => '5', 'author_name' => 'Anna Beispiel', 'author_role' => 'Head of Marketing, Beispiel AG', '_files' => ['image' => ['square-1.jpg']]],
        ['quote' => '<p>Clean code, great performance and a design system that just works. Our Lighthouse scores have never been better.</p>', 'rating' => '5', 'author_name' => 'Paul Probe', 'author_role' => 'Lead Developer, Probewerk', '_files' => ['image' => ['square-2.jpg']]],
        ['quote' => '<p>The dark mode and theme settings let us match our brand in minutes instead of days. Highly recommended.</p>', 'rating' => '5', 'author_name' => 'Petra Platzhalter', 'author_role' => 'Creative Director, Testhaus', '_files' => ['image' => ['square-3.jpg']]],
    ]],
    '_flexform' => ['sDEF' => ['settings.layout' => 'grid', 'settings.columns' => '3', 'settings.showRating' => '1']],
];

$logoWall = [
    'CType' => 'conn_logo_wall',
    'header' => 'Trusted by teams everywhere',
    'header_layout' => '3',
    'header_position' => 'center',
    'space_before_class' => 'large',
    '_inline' => ['tx_conn_t3wtk_elements_logo_wall_items' => array_map(
        static fn(string $name, string $file) => ['alt' => $name, '_files' => ['image' => ['logo-' . $file . '.svg']]],
        ['Musterfirma', 'Beispiel AG', 'Probewerk', 'Testhaus', 'Vorlage & Co', 'Platzhalter'],
        ['musterfirma', 'beispiel', 'probewerk', 'testhaus', 'vorlage', 'platzhalter']
    )],
    '_flexform' => ['sDEF' => ['settings.layout' => 'marquee', 'settings.speed' => '30', 'settings.grayscale' => '1']],
];

$faq = [
    'CType' => 'conn_bs_accordion',
    'header' => 'Frequently asked questions',
    'header_layout' => '2',
    'header_position' => 'center',
    'subheader' => 'FAQ',
    'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
    'space_before_class' => 'extra-large',
    '_inline' => ['tx_conn_t3wtk_elements_accordion_items' => [
        ['title' => 'What is t3wtk?', 'body' => $p('t3wtk is a set of TYPO3 extensions — base, elements and sitepackage — that gives you a complete, themeable website toolkit for TYPO3 LTS 14.')],
        ['title' => 'Do I need to write code to change the design?', 'body' => $p('No. Colors, logo, navigation, footer and dark mode are site settings. For deeper changes the sitepackage exposes every design token as a CSS custom property.')],
        ['title' => 'How do I get rid of this demo content?', 'body' => $p('Delete the demo page tree and the site configuration "t3kit-demosite" — or simply start editing: it is regular TYPO3 content.')],
        ['title' => 'Is the output accessible?', 'body' => $p('All elements use semantic markup, proper heading levels and keyboard-accessible interactive widgets built on Bootstrap 5.')],
    ]],
    '_flexform' => ['sDEF' => ['settings.style' => 'flush', 'settings.useAsFaq' => '1']],
];

// ── Page tree ──────────────────────────────────────────────────────────────

$pages = [[
    '_key' => 'home',
    'title' => 'Home',
    'description' => 'Demo site of the Connetation TYPO3 Web Toolkit (t3wtk): themeable content elements, dark mode and site settings for TYPO3 LTS 14.',
    'is_siteroot' => 1,
    'backend_layout' => 'pagets__conn_topcontent',
    'backend_layout_next_level' => 'pagets__conn_default',
    'tx_conn_t3wtk_base_hide_breadcrumb' => 1,
    '_content' => [
        // Top content: hero slider
        [
            'CType' => 'conn_swiper_hero_slider',
            'colPos' => 1,
            'header' => 'Welcome to t3wtk',
            'header_layout' => '100',
            'frame_class' => 'none',
            'tx_conn_t3wtk_base_content_width' => 'fullbleed',
            // Directly below the navbar: no default spacer above the slider
            'space_before_class' => 'none',
            '_inline' => ['tx_conn_t3wtk_elements_hero_slider_items' => [
                $slide('hero-indigo.jpg', 'Build beautiful TYPO3 websites', 'A complete toolkit of themeable content elements for TYPO3 LTS 14.', 'elements', 'Explore the elements', ['text_animation' => 'words', 'bg_animation' => 'zoom']),
                $slide('hero-ocean.jpg', 'Design once, theme everywhere', 'Colors, logo, navigation and dark mode — all configurable as site settings.', 'features', 'See the features', ['text_animation' => 'lines', 'bg_animation' => 'zoom', 'text_pos_h' => 'center', 'text_pos_v' => 'center']),
                $slide('hero-sunset.jpg', 'Fast, accessible, editor-friendly', 'Semantic markup, great Lighthouse scores and a backend editors enjoy.', 'contact', 'Get in touch', ['text_animation' => 'chars', 'bg_animation' => 'zoom']),
            ]],
            '_flexform' => ['sDEF' => ['settings.height' => '80', 'settings.effect' => 'fade', 'settings.autoplay' => '1', 'settings.autoplay_delay' => '5000']],
        ],
        $intro('t3wtk demo', 'Everything you need for a modern TYPO3 website', 'This demo site was created automatically when the t3wtk demo extension was installed. Every page shows real content elements from connetation/t3wtk-elements — open the page module and see how they are configured.', '2'),
        [
            'CType' => 'conn_feature_grid',
            'header' => 'Why t3wtk?',
            'header_layout' => '100',
            '_inline' => ['tx_conn_t3wtk_elements_feature_grid_items' => [
                $feature('grid-1x2', '30+ content elements', 'Sliders, galleries, cards, pricing tables, timelines and more — ready for editors.', 'elements', 'Explore all content elements'),
                $feature('palette', 'Themeable', 'Every color, the logo, navbar and footer are site settings. Dark mode included.', 'features', 'Discover the theming features'),
                $feature('lightning-charge', 'Fast by default', 'Lean CSS and JavaScript, lazy loading and responsive images out of the box.'),
                $feature('universal-access', 'Accessible', 'Semantic HTML, proper heading levels and keyboard-friendly widgets.'),
                $feature('phone', 'Responsive', 'Built on Bootstrap 5 — looks great on every screen size.'),
                $feature('puzzle', 'Extensible', 'Override any template in your sitepackage without forking the toolkit.'),
            ]],
            '_flexform' => ['sDEF' => ['settings.columns' => '3', 'settings.variant' => 'default', 'settings.iconStyle' => 'soft', 'settings.accentBar' => '1']],
        ],
        $stats,
        [
            'CType' => 'conn_grid_2col',
            'header' => 'Built for editors',
            'header_layout' => '100',
            'space_before_class' => 'extra-large',
            '_children' => [
                200 => [[
                    'CType' => 'text',
                    'header' => 'Built for editors, loved by developers',
                    'header_layout' => '2',
                    'subheader' => 'The toolkit',
                    'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
                    'bodytext' => $p('Editors get a clean page module with clearly named fields and live previews. Developers get well-structured Fluid templates, Site Sets and a sitepackage that keeps project code separate from the toolkit.')
                        . $ul('Site Sets for TYPO3 LTS 14', 'Light and dark theme per site', 'Container-based column layouts', 'Per-element backgrounds, spacing and animations')
                        . '<p>' . $button('Browse all elements', 'elements') . '</p>',
                ]],
                201 => [[
                    'CType' => 'conn_card_stack',
                    'header' => 'Card stack',
                    'header_layout' => '100',
                    '_inline' => ['tx_conn_t3wtk_elements_card_stack_items' => [
                        ['title' => 'Plan', 'subtitle' => 'Step 1', 'body' => $p('Pick a layout and the elements you need.'), '_files' => ['image' => ['card-violet.jpg']]],
                        ['title' => 'Build', 'subtitle' => 'Step 2', 'body' => $p('Fill in content — no code required.'), '_files' => ['image' => ['card-teal.jpg']]],
                        ['title' => 'Launch', 'subtitle' => 'Step 3', 'body' => $p('Publish a fast, accessible website.'), '_files' => ['image' => ['card-amber.jpg']]],
                    ]],
                    '_flexform' => ['sDEF' => ['settings.textPosition' => 'bottom', 'settings.navPosition' => 'bottom', 'settings.autoplay' => '1']],
                ]],
            ],
        ],
        [
            'CType' => 'conn_swiper_slider',
            'header' => 'Latest projects',
            'header_layout' => '2',
            'header_position' => 'center',
            'subheader' => 'Showcase',
            'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
            '_inline' => ['tx_conn_t3wtk_elements_slider_items' => [
                $slide('card-violet.jpg', 'Brand relaunch', 'Corporate website', 'sliders'),
                $slide('card-teal.jpg', 'Online magazine', 'Publishing', 'sliders'),
                $slide('card-amber.jpg', 'Product platform', 'E-commerce', 'sliders'),
                $slide('card-rose.jpg', 'Event microsite', 'Campaign', 'sliders'),
                $slide('card-sky.jpg', 'Intranet', 'Internal communication', 'sliders'),
            ]],
            '_flexform' => ['sDEF' => ['settings.slides_per_view' => '3', 'settings.loop' => '1', 'settings.autoplay' => '1']],
        ] + $softBand(),
        $testimonials,
        $logoWall,
        $cta('Ready to build your next website?', 'Install t3wtk, pick your colors and start creating content today.'),
    ],
    '_subpages' => [
        [
            '_key' => 'features',
            'title' => 'Features',
            'description' => 'How t3wtk-base, t3wtk-elements and your sitepackage work together: site settings, dark mode, column layouts, backgrounds, animations and forms.',
            '_content' => [
                $intro('Features', 'A toolkit, not a template', 'Three extensions work together: connetation/t3wtk-base provides the design system and page templates, connetation/t3wtk-elements the content elements, and your sitepackage the project-specific touches.'),
                [
                    'CType' => 'conn_grid_3col',
                    'header' => 'Packages',
                    'header_layout' => '2',
                    'tx_conn_t3wtk_base_header_class' => 'visually-hidden',
                    '_children' => [
                        210 => [$textIcon('layers', 't3wtk-base', 'Page templates, navigation, footer, design tokens, light/dark theme and site settings.')],
                        211 => [$textIcon('grid-3x3-gap', 't3wtk-elements', 'More than 30 content elements — sliders, galleries, cards, data and interactive widgets.')],
                        212 => [$textIcon('box-seam', 't3wtk-sitepackage', 'Your project layer: theme overrides, forms, email templates and SEO configuration.')],
                    ],
                ],
                [
                    'CType' => 'conn_feature_grid',
                    'header' => 'Highlights',
                    'header_layout' => '2',
                    'header_position' => 'center',
                    'subheader' => 'Under the hood',
                    'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
                    '_inline' => ['tx_conn_t3wtk_elements_feature_grid_items' => [
                        $feature('sliders', 'Site settings', 'Configure colors, logo, navigation and footer in the Site Management module.'),
                        $feature('moon-stars', 'Dark mode', 'A full dark palette with a theme toggle — per site, no extra CSS.'),
                        $feature('columns-gap', 'Column layouts', 'Five container layouts to arrange any element side by side.'),
                        $feature('image', 'Backgrounds', 'Color, image and parallax backgrounds for every content element.'),
                        $feature('magic', 'Animations', 'Text and scroll animations powered by GSAP — just pick one.'),
                        $feature('envelope-paper', 'Forms & emails', 'A contact form with validation and branded email templates.'),
                    ]],
                    '_flexform' => ['sDEF' => ['settings.columns' => '3', 'settings.variant' => 'horizontal', 'settings.iconStyle' => 'solid']],
                ] + $softBand(),
                [
                    'CType' => 'conn_process',
                    'header' => 'From install to launch',
                    'header_layout' => '2',
                    'header_position' => 'center',
                    'subheader' => 'How it works',
                    'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
                    'space_before_class' => 'extra-large',
                    '_inline' => ['tx_conn_t3wtk_elements_process_items' => [
                        ['title' => 'Install', 'icon' => 'download', 'body' => $p('Require the t3wtk packages via Composer.')],
                        ['title' => 'Configure', 'icon' => 'sliders', 'body' => $p('Add the Site Set and set your colors and logo.')],
                        ['title' => 'Create', 'icon' => 'pencil-square', 'body' => $p('Build pages with the content elements.')],
                        ['title' => 'Launch', 'icon' => 'rocket-takeoff', 'body' => $p('Go live with a fast, accessible site.')],
                    ]],
                    '_flexform' => ['sDEF' => ['settings.numbered' => '1', 'settings.connectorStyle' => 'dashed', 'settings.maxPerRow' => '4']],
                ],
                $faq,
                $cta('See every element in action', 'Browse the element showcase to see what you can build.', 'Browse elements', 'elements'),
            ],
        ],
        [
            '_key' => 'elements',
            'title' => 'Elements',
            'description' => 'Showcase of all t3wtk content elements for TYPO3 — text, cards, sliders, galleries, interactive widgets and data elements with real content.',
            '_content' => [
                $intro('Showcase', 'Content elements', 'Every element from connetation/t3wtk-elements, grouped by purpose. Pick a group to see the elements with real content.'),
                [
                    'CType' => 'conn_subpage_cards',
                    'header' => 'Element groups',
                    'header_layout' => '2',
                    'tx_conn_t3wtk_base_header_class' => 'visually-hidden',
                    'tx_conn_t3wtk_elements_subpage_cards_source' => '',
                    '_flexform' => [
                        'sDEF' => ['settings.columns' => '3', 'settings.style' => 'shadow', 'settings.accentBar' => '1'],
                        'sContent' => ['settings.showImage' => '1', 'settings.showTeaser' => '1', 'settings.showLink' => '1', 'settings.linkLabel' => 'View elements'],
                    ],
                ],
            ],
            '_subpages' => [
                $showcasePage('text', 'Text & Typography', 'Text, columns, quotes, icons and alerts — the building blocks of every page.', 'card-violet.jpg', [
                    $intro('Text & Typography', 'Text elements', 'Headers with eyebrows, rich text, multi-column text, quotes, text with icons and alert banners.'),
                    [
                        'CType' => 'textmedia',
                        'header' => 'Text & Media',
                        'header_layout' => '2',
                        'subheader' => 'Core element',
                        'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
                        'bodytext' => $p('The core Text &amp; Media element, styled by t3wtk-base. Place images above, below or next to the text — here the image sits on the right.', $lorem) . $ul('Rich text editor with t3wtk styles', 'Responsive images', 'Optional lightbox'),
                        'imageorient' => 25,
                        'imagecols' => 2, // t3wtk-base: beside-text split = 1/cols for the image
                        '_files' => ['assets' => [['file' => 'card-sky.jpg', 'alternative' => 'Abstract blue gradient']]],
                    ],
                    [
                        'CType' => 'conn_text_column',
                        'header' => 'Text in columns',
                        'header_layout' => '2',
                        'space_before_class' => 'large',
                        'bodytext' => $p('Long texts are easier to read when they flow into columns. The Text (Columns) element splits rich text into CSS columns from tablet width upwards, while phones keep a single, comfortable column.', 'Use it for introductions, legal texts or any longer copy. Headlines, lists and links inside the text work just like in the regular text element.', $lorem, 'Because the columns are pure CSS, the text reflows automatically when the viewport changes — no manual balancing needed.'),
                    ],
                    [
                        'CType' => 'conn_quote',
                        'header' => 'Quote',
                        'header_layout' => '100',
                        'bodytext' => $p('Good design is invisible — people only notice it when it is missing.'),
                        'tx_conn_t3wtk_elements_quote_author' => 'Erika Musterfrau',
                        'tx_conn_t3wtk_elements_quote_role' => 'Managing Director, Musterfirma',
                        '_flexform' => ['sDEF' => ['settings.style' => 'container', 'settings.align' => 'center', 'settings.textSize' => 'large']],
                    ] + $imageBand('hero-sunset.jpg'),
                    $section('Text Icon', 'Text with icons'),
                    [
                        'CType' => 'conn_grid_3col',
                        'header' => 'Text icons',
                        'header_layout' => '100',
                        '_children' => [
                            210 => [$textIcon('shield-check', 'Secure', 'Regular TYPO3 security updates and a clean, maintainable codebase.', 'circle')],
                            211 => [$textIcon('speedometer2', 'Fast', 'Optimized assets and caching for top performance scores.', 'square')],
                            212 => [$textIcon('heart', 'Loved', 'Editors enjoy working with clear and simple forms.', 'hexagon')],
                        ],
                    ],
                    $section('Alert', 'Alerts & notices'),
                    ['CType' => 'conn_alert', 'header' => 'Did you know?', 'bodytext' => $p('All colors in this demo come from the site settings — change them in Site Management → Settings.'), '_flexform' => ['sDEF' => ['settings.style' => 'info']], 'space_after_class' => 'small'],
                    ['CType' => 'conn_alert', 'header' => 'Success', 'bodytext' => $p('The demo site was installed successfully.'), '_flexform' => ['sDEF' => ['settings.style' => 'success']], 'space_after_class' => 'small'],
                    ['CType' => 'conn_alert', 'header' => 'Heads up', 'bodytext' => $p('This is demo content. Replace it before going live.'), '_flexform' => ['sDEF' => ['settings.style' => 'warning'], 'dismiss' => ['settings.dismissible' => '1']], 'space_after_class' => 'small'],
                    ['CType' => 'conn_alert', 'header' => 'Custom style', 'bodytext' => $p('Alerts can use any icon and your own colors.'), 'tx_conn_t3wtk_elements_alert_custom_icon_name' => 'stars', '_flexform' => ['sDEF' => ['settings.style' => 'custom'], 'custom' => ['settings.customAccentColor' => '#8b5cf6', 'settings.customBackgroundColor' => '#8b5cf61a']]],
                ]),
                $showcasePage('cards', 'Cards & Layout', 'Card groups, feature grids, stacked cards and container-based column layouts.', 'card-teal.jpg', [
                    $intro('Cards & Layout', 'Cards and columns', 'Arrange content in cards and grids. Container elements put any element side by side.'),
                    [
                        'CType' => 'conn_bs_card_group',
                        'header' => 'Card Group',
                        'header_layout' => '2',
                        'subheader' => 'Bootstrap cards with images, badges and links',
                        '_inline' => ['tx_conn_t3wtk_elements_card_group_items' => [
                            $card('card-violet.jpg', 'Strategy', 'We analyse your goals and audience and define the right concept.', 'features', 'New'),
                            $card('card-teal.jpg', 'Design', 'A clean, consistent design system that matches your brand.', 'features'),
                            $card('card-amber.jpg', 'Development', 'Fast, accessible TYPO3 websites built on proven components.', 'features'),
                        ]],
                        '_flexform' => ['sDEF' => ['settings.columns' => '3', 'settings.style' => 'shadow', 'settings.accentBar' => '1']],
                    ],
                    [
                        'CType' => 'conn_feature_grid',
                        'header' => 'Feature Grid — steps variant',
                        'header_layout' => '2',
                        'space_before_class' => 'extra-large',
                        '_inline' => ['tx_conn_t3wtk_elements_feature_grid_items' => [
                            $feature('chat-dots', 'Talk', 'Tell us about your project and goals.'),
                            $feature('vector-pen', 'Design', 'We create a concept and a design proposal.'),
                            $feature('code-slash', 'Build', 'We implement it with t3wtk and TYPO3.'),
                            $feature('graph-up-arrow', 'Grow', 'We measure, learn and improve together.'),
                        ]],
                        '_flexform' => ['sDEF' => ['settings.columns' => '4', 'settings.variant' => 'steps', 'settings.iconStyle' => 'soft']],
                    ] + $softBand(),
                    $section('Containers', 'Column layouts', 'Five container elements: 2, 3 and 4 equal columns, plus narrow/wide combinations.'),
                    [
                        'CType' => 'conn_grid_4_8',
                        'header' => 'Narrow + Wide',
                        'header_layout' => '100',
                        '_children' => [
                            230 => [['CType' => 'image', 'header' => 'Image', 'header_layout' => '100', '_files' => ['image' => [['file' => 'square-4.jpg', 'alternative' => 'Abstract pink gradient']]]]],
                            231 => [[
                                'CType' => 'text',
                                'header' => 'Narrow + Wide (4/8)',
                                'header_layout' => '3',
                                'bodytext' => $p('An image in the narrow column and text in the wide one. Containers accept any content element — even other containers.', $lorem),
                            ]],
                        ],
                    ],
                    [
                        'CType' => 'conn_grid_4col',
                        'header' => 'Four columns',
                        'header_layout' => '100',
                        'space_before_class' => 'large',
                        '_children' => [
                            220 => [$textIcon('1-circle', 'Column 1', 'Four equal columns on desktop.', 'none')],
                            221 => [$textIcon('2-circle', 'Column 2', 'Two columns on tablets.', 'none')],
                            222 => [$textIcon('3-circle', 'Column 3', 'One column on phones.', 'none')],
                            223 => [$textIcon('4-circle', 'Column 4', 'Fully responsive.', 'none')],
                        ],
                    ],
                    [
                        'CType' => 'conn_card_stack',
                        'header' => 'Card Stack',
                        'header_layout' => '2',
                        'header_position' => 'center',
                        'space_before_class' => 'extra-large',
                        '_inline' => ['tx_conn_t3wtk_elements_card_stack_items' => [
                            ['title' => 'Swipe through', 'subtitle' => 'Card 1', 'body' => $p('A stacked deck of cards with smooth GSAP animations.'), '_files' => ['image' => ['card-rose.jpg']]],
                            ['title' => 'Any content', 'subtitle' => 'Card 2', 'body' => $p('Title, subtitle, text and an image per card.'), '_files' => ['image' => ['card-sky.jpg']]],
                            ['title' => 'Autoplay', 'subtitle' => 'Card 3', 'body' => $p('Optionally cycles through the cards automatically.'), '_files' => ['image' => ['card-emerald.jpg']]],
                        ]],
                        '_flexform' => ['sDEF' => ['settings.textPosition' => 'center', 'settings.textAlign' => 'center', 'settings.navPosition' => 'sides']],
                    ],
                ]),
                $showcasePage('sliders', 'Sliders', 'Hero, card, thumbnail and parallax sliders, a Bootstrap carousel and a before/after comparison.', 'card-amber.jpg', [
                    $intro('Sliders', 'Sliders and carousels', 'Swiper-based sliders with text animations, a Bootstrap carousel and a drag-to-compare image slider.'),
                    [
                        'CType' => 'conn_swiper_parallax_slider',
                        'header' => 'Parallax Slider',
                        'header_layout' => '100',
                        'frame_class' => 'none',
                        'tx_conn_t3wtk_base_content_width' => 'fullbleed',
                        '_files' => ['tx_conn_t3wtk_elements_parallax_slider_bg_image' => ['hero-ocean.jpg']],
                        '_inline' => ['tx_conn_t3wtk_elements_parallax_slider_items' => [
                            ['title' => 'Parallax Slider', 'subtitle' => 'One background, many slides', 'body' => $p('The background moves slower than the text for a subtle depth effect.'), 'text_animation' => 'words'],
                            ['title' => 'Smooth transitions', 'subtitle' => 'Powered by Swiper', 'body' => $p('Touch, keyboard and mouse navigation built in.'), 'text_animation' => 'lines'],
                            ['title' => 'Your story', 'subtitle' => 'Slide three', 'body' => $p('Use it for product tours, timelines or campaigns.'), 'link' => 't3://page?uid={page:contact}', 'link_label' => 'Contact us', 'text_animation' => 'chars'],
                        ]],
                        '_flexform' => ['sDEF' => ['settings.height' => 'large', 'settings.show_pagination' => '1']],
                    ],
                    [
                        'CType' => 'conn_bs_carousel',
                        'header' => 'Bootstrap Carousel',
                        'header_layout' => '2',
                        'space_before_class' => 'extra-large',
                        '_inline' => ['tx_conn_t3wtk_elements_carousel_items' => [
                            ['title' => 'First slide', 'title_layout' => '3', 'caption' => $p('Captions with title and text.'), '_files' => ['image' => ['hero-indigo.jpg']]],
                            ['title' => 'Second slide', 'title_layout' => '3', 'caption' => $p('Optional fade effect and autoplay.'), '_files' => ['image' => ['hero-sunset.jpg']]],
                            ['title' => 'Third slide', 'title_layout' => '3', 'caption' => $p('Opens in a lightbox if you like.'), '_files' => ['image' => ['hero-ocean.jpg']]],
                        ]],
                        '_flexform' => ['sDEF' => ['settings.aspectRatio' => '21x9', 'settings.fade' => '1', 'settings.autoplay' => '1', 'settings.style' => 'shadow']],
                    ],
                    [
                        'CType' => 'conn_swiper_thumbs_slider',
                        'header' => 'Thumbnails Slider',
                        'header_layout' => '2',
                        'space_before_class' => 'extra-large',
                        '_inline' => ['tx_conn_t3wtk_elements_thumbs_slider_items' => [
                            $slide('card-violet.jpg', 'Violet', 'Slide one'),
                            $slide('card-teal.jpg', 'Teal', 'Slide two'),
                            $slide('card-amber.jpg', 'Amber', 'Slide three'),
                            $slide('card-rose.jpg', 'Rose', 'Slide four'),
                            $slide('card-sky.jpg', 'Sky', 'Slide five'),
                            $slide('card-emerald.jpg', 'Emerald', 'Slide six'),
                        ]],
                        '_flexform' => ['sDEF' => ['settings.height' => 'medium', 'settings.thumbs_per_view' => '6', 'settings.openInLightbox' => '1']],
                    ],
                    [
                        'CType' => 'conn_before_after',
                        'header' => 'Before / After',
                        'header_layout' => '2',
                        'subheader' => 'Drag the handle to compare',
                        'space_before_class' => 'extra-large',
                        'tx_conn_t3wtk_elements_before_after_before_label' => 'Before',
                        'tx_conn_t3wtk_elements_before_after_after_label' => 'After',
                        'tx_conn_t3wtk_elements_before_after_start' => 50,
                        '_files' => [
                            'tx_conn_t3wtk_elements_before_after_before' => ['compare-before.jpg'],
                            'tx_conn_t3wtk_elements_before_after_after' => ['compare-after.jpg'],
                        ],
                    ],
                ]),
                $showcasePage('galleries', 'Galleries', 'Mosaic and lightbox galleries, a filterable shift carousel and a logo wall.', 'card-rose.jpg', [
                    $intro('Galleries', 'Galleries', 'Show images in masonry, justified or metro layouts, expand them in a lightbox or filter them by category.'),
                    [
                        'CType' => 'conn_mosaic_gallery',
                        'header' => 'Mosaic Gallery',
                        'header_layout' => '2',
                        '_inline' => ['tx_conn_t3wtk_elements_mosaic_gallery_items' => array_map(
                            static fn(array $item) => ['title' => $item[1], '_files' => ['image' => [$item[0]]]],
                            [['hero-indigo.jpg', 'Indigo'], ['square-1.jpg', 'Square one'], ['card-teal.jpg', 'Teal'], ['square-2.jpg', 'Square two'], ['hero-sunset.jpg', 'Sunset'], ['card-amber.jpg', 'Amber'], ['square-3.jpg', 'Square three'], ['card-sky.jpg', 'Sky']]
                        )],
                        '_flexform' => ['sDEF' => ['settings.layoutType' => 'masonry', 'settings.columns' => '4', 'settings.gap' => 'medium'], 'sDisplay' => ['settings.hoverEffect' => 'zoom', 'settings.captionStyle' => 'overlay']],
                    ],
                    [
                        'CType' => 'conn_lightbox_gallery',
                        'header' => 'Lightbox Gallery',
                        'header_layout' => '2',
                        'subheader' => 'Click an image to expand it',
                        'space_before_class' => 'extra-large',
                        '_inline' => ['tx_conn_t3wtk_elements_lightbox_gallery_items' => array_map(
                            static fn(array $item) => ['title' => $item[1], 'caption' => '<p>' . $item[2] . '</p>', '_files' => ['image' => [$item[0]]]],
                            [['card-violet.jpg', 'Violet', 'Deep purple tones.'], ['card-emerald.jpg', 'Emerald', 'Fresh greens.'], ['card-rose.jpg', 'Rose', 'Warm pinks.'], ['card-sky.jpg', 'Sky', 'Light blues.'], ['card-amber.jpg', 'Amber', 'Golden hues.'], ['card-teal.jpg', 'Teal', 'Calm cyan.']]
                        )],
                        '_flexform' => ['sDEF' => ['settings.columns' => '3', 'settings.gap' => 'medium'], 'border' => ['settings.borderStyle' => 'none']],
                    ],
                    [
                        'CType' => 'conn_shift_carousel',
                        'header' => 'Shift Carousel',
                        'header_layout' => '2',
                        'subheader' => 'Cards with category, text and link',
                        'space_before_class' => 'extra-large',
                        '_inline' => ['tx_conn_t3wtk_elements_shift_carousel_items' => [
                            ['title' => 'Corporate site', 'subtitle' => 'Web', 'category' => 'Web', 'body' => $p('A multi-language corporate website.'), '_files' => ['image' => ['card-violet.jpg']]],
                            ['title' => 'Brand identity', 'subtitle' => 'Branding', 'category' => 'Branding', 'body' => $p('Logo, colors and typography.'), '_files' => ['image' => ['card-amber.jpg']]],
                            ['title' => 'Online shop', 'subtitle' => 'Web', 'category' => 'Web', 'body' => $p('Product catalogue with checkout.'), '_files' => ['image' => ['card-teal.jpg']]],
                            ['title' => 'Mobile app', 'subtitle' => 'Apps', 'category' => 'Apps', 'body' => $p('Companion app for iOS and Android.'), '_files' => ['image' => ['card-sky.jpg']]],
                            ['title' => 'Campaign', 'subtitle' => 'Branding', 'category' => 'Branding', 'body' => $p('Launch campaign across channels.'), '_files' => ['image' => ['card-rose.jpg']]],
                            ['title' => 'Dashboard', 'subtitle' => 'Apps', 'category' => 'Apps', 'body' => $p('Data dashboard for internal teams.'), '_files' => ['image' => ['card-emerald.jpg']]],
                        ]],
                        '_flexform' => ['sDEF' => ['settings.columns' => '3']],
                    ] + $softBand(),
                    array_replace($logoWall, [
                        'header' => 'Logo Wall',
                        'header_layout' => '2',
                        'header_position' => '',
                        'space_before_class' => 'extra-large',
                        '_flexform' => ['sDEF' => ['settings.layout' => 'grid', 'settings.columns' => '6', 'settings.itemStyle' => 'card', 'settings.grayscale' => '1']],
                    ]),
                ]),
                $showcasePage('interactive', 'Interactive', 'Accordions, tabs, process steps and timelines.', 'card-sky.jpg', [
                    $intro('Interactive', 'Interactive elements', 'Structure information with accordions, tabs, step-by-step processes and timelines.'),
                    [
                        'CType' => 'conn_bs_tabs',
                        'header' => 'Tabs',
                        'header_layout' => '2',
                        '_inline' => ['tx_conn_t3wtk_elements_tabs_items' => [
                            ['title' => 'Overview', 'body' => $p('Tabs keep related content compact. Each tab has a title and rich text.', $lorem)],
                            ['title' => 'Details', 'body' => $p('Choose between classic tabs and pills, optionally justified and with a fade animation.')],
                            ['title' => 'Specs', 'body' => $ul('Bootstrap 5 tabs', 'Keyboard accessible', 'Up to 200 tabs per element')],
                        ]],
                        '_flexform' => ['sDEF' => ['settings.style' => 'connected', 'settings.fade' => '1', 'settings.justified' => '1']],
                    ],
                    array_replace($faq, ['header' => 'Accordion', 'header_position' => '', 'subheader' => '', 'tx_conn_t3wtk_base_subheader_eyebrow' => 0, '_flexform' => ['sDEF' => ['settings.style' => 'default']]]),
                    [
                        'CType' => 'conn_process',
                        'header' => 'Process / Steps',
                        'header_layout' => '2',
                        'space_before_class' => 'extra-large',
                        '_inline' => ['tx_conn_t3wtk_elements_process_items' => [
                            ['title' => 'Discovery', 'icon' => 'search', 'body' => $p('Understand goals and users.')],
                            ['title' => 'Concept', 'icon' => 'diagram-3', 'body' => $p('Structure and wireframes.')],
                            ['title' => 'Design', 'icon' => 'brush', 'body' => $p('Visual design and prototypes.')],
                            ['title' => 'Build', 'icon' => 'tools', 'body' => $p('Development and content.')],
                            ['title' => 'Launch', 'icon' => 'flag', 'body' => $p('Go live and iterate.')],
                        ]],
                        '_flexform' => ['sDEF' => ['settings.numbered' => '1', 'settings.connectorStyle' => 'solid', 'settings.maxPerRow' => '5']],
                    ] + $softBand(),
                    [
                        'CType' => 'conn_timeline',
                        'header' => 'Timeline',
                        'header_layout' => '2',
                        'space_before_class' => 'extra-large',
                        '_inline' => ['tx_conn_t3wtk_elements_timeline_items' => [
                            ['date' => mktime(0, 0, 0, 1, 1, 2023), 'date_precision' => 'year', 'title' => 'The idea', 'body' => $p('A shared toolkit instead of starting every project from scratch.')],
                            ['date' => mktime(0, 0, 0, 1, 1, 2024), 'date_precision' => 'year', 'title' => 'First release', 'body' => $p('Base templates and the first content elements.')],
                            ['date' => mktime(0, 0, 0, 1, 1, 2025), 'date_precision' => 'year', 'title' => 'Elements', 'body' => $p('Sliders, galleries and data elements join the family.')],
                            ['date' => mktime(0, 0, 0, 1, 1, 2026), 'date_precision' => 'year', 'title' => 'TYPO3 LTS 14', 'body' => $p('Site Sets, dark mode and this demo site.')],
                        ]],
                        '_flexform' => ['sDEF' => ['settings.layout' => 'responsive', 'settings.line_style' => 'solid']],
                    ],
                ]),
                $showcasePage('data', 'Data & People', 'Stats, pricing tables, testimonials, team members and maps.', 'card-emerald.jpg', [
                    $intro('Data & People', 'Data and people', 'Present numbers, prices, opinions and the people behind your organisation.'),
                    array_replace($stats, ['header' => 'Stats / Counters', 'space_before_class' => '']),
                    [
                        'CType' => 'conn_people',
                        'header' => 'People',
                        'header_layout' => '2',
                        'header_position' => 'center',
                        'space_before_class' => 'extra-large',
                        '_flexform' => [
                            'sDEF' => ['settings.source' => 'folder', 'settings.folder' => '{page:records}'],
                            'display' => ['settings.style' => 'team-card', 'settings.columns' => '4', 'settings.showRole' => '1', 'settings.showBio' => '1'],
                        ],
                    ],
                    array_replace($testimonials, ['header' => 'Testimonials', 'subheader' => '', 'tx_conn_t3wtk_base_subheader_eyebrow' => 0, '_flexform' => ['sDEF' => ['settings.layout' => 'carousel', 'settings.columns' => '2', 'settings.autoplay' => '1']]]),
                    [
                        'CType' => 'conn_map',
                        'header' => 'Map & Location',
                        'header_layout' => '2',
                        'space_before_class' => 'extra-large',
                        '_files' => ['tx_conn_t3wtk_elements_map_illustration_image' => ['map-connetation.png']],
                        '_flexform' => ['sDEF' => ['settings.provider' => 'static', 'settings.showGridOverImage' => '0', 'settings.height' => 'md'], 'panel' => ['settings.panelPosition' => 'right']],
                    ],
                ]),
            ],
        ],
        [
            '_key' => 'about',
            'title' => 'About',
            'description' => 'Example about page built with t3wtk: company story, team members, key figures and a milestone timeline.',
            '_content' => [
                $intro('About us', 'We build websites people enjoy using', 'A fictional agency to show how an about page can look with t3wtk. Replace this text with your own story.'),
                [
                    'CType' => 'conn_grid_8_4',
                    'header' => 'Our story',
                    'header_layout' => '100',
                    '_children' => [
                        240 => [[
                            'CType' => 'text',
                            'header' => 'Our story',
                            'header_layout' => '2',
                            'bodytext' => $p('What started as a small team of designers and developers has grown into a studio that builds websites for companies of every size.', 'We believe good websites are fast, accessible and easy to maintain — for the people who visit them and for the editors who keep them up to date.'),
                        ]],
                        241 => [[
                            'CType' => 'conn_quote',
                            'header' => 'Mission',
                            'header_layout' => '100',
                            'bodytext' => $p('Make the web a little faster and friendlier — one website at a time.'),
                            'tx_conn_t3wtk_elements_quote_author' => 'The t3wtk team',
                            '_flexform' => ['sDEF' => ['settings.style' => 'mark', 'settings.align' => 'left']],
                        ]],
                    ],
                ],
                [
                    'CType' => 'conn_people',
                    'header' => 'Meet the team',
                    'header_layout' => '2',
                    'header_position' => 'center',
                    'subheader' => 'Team',
                    'tx_conn_t3wtk_base_subheader_eyebrow' => 1,
                    'space_before_class' => 'extra-large',
                    '_flexform' => [
                        'sDEF' => ['settings.source' => 'folder', 'settings.folder' => '{page:records}'],
                        'display' => ['settings.style' => 'reveal-sheet', 'settings.columns' => '4'],
                    ],
                ] + $softBand(),
                array_replace($stats, ['space_before_class' => 'extra-large']),
                [
                    'CType' => 'conn_timeline',
                    'header' => 'Milestones',
                    'header_layout' => '2',
                    'header_position' => 'center',
                    'space_before_class' => 'extra-large',
                    '_inline' => ['tx_conn_t3wtk_elements_timeline_items' => [
                        ['date' => mktime(0, 0, 0, 3, 1, 2019), 'date_precision' => 'year', 'title' => 'Founded', 'body' => $p('Three people, one office, lots of ideas.'), '_files' => ['image' => ['card-violet.jpg']]],
                        ['date' => mktime(0, 0, 0, 6, 1, 2021), 'date_precision' => 'year', 'title' => '100th website', 'body' => $p('A milestone we celebrated with the whole team.'), '_files' => ['image' => ['card-amber.jpg']]],
                        ['date' => mktime(0, 0, 0, 9, 1, 2024), 'date_precision' => 'year', 'title' => 'New studio', 'body' => $p('More space for a growing team.'), '_files' => ['image' => ['card-teal.jpg']]],
                    ]],
                    '_flexform' => ['sDEF' => ['settings.layout' => 'changelog', 'settings.position' => 'center']],
                ],
                $cta('Want to work with us?', 'We are always happy to talk about new projects.'),
            ],
        ],
        [
            '_key' => 'pricing',
            'title' => 'Pricing',
            'description' => 'Example pricing page built with t3wtk: pricing table with featured tier, logo wall and frequently asked questions.',
            '_content' => [
                $intro('Pricing', 'Simple, transparent pricing', 'Example pricing tiers — the Pricing Table element supports featured tiers, badges and unavailable features.'),
                [
                    'CType' => 'conn_bs_pricing',
                    'header' => 'Plans',
                    'header_layout' => '2',
                    'tx_conn_t3wtk_base_header_class' => 'visually-hidden',
                    '_inline' => ['tx_conn_t3wtk_elements_pricing_tiers' => [
                        ['name' => 'Starter', 'tagline' => 'For small websites', 'currency' => '€', 'amount' => '29', 'period' => 'per month', 'features' => "Up to 10 pages\nAll content elements\nEmail support\n-Multi-language\n-Custom design", 'cta_label' => 'Choose Starter', 'cta_link' => 't3://page?uid={page:contact}'],
                        ['name' => 'Business', 'tagline' => 'For growing companies', 'currency' => '€', 'amount' => '79', 'period' => 'per month', 'features' => "Unlimited pages\nAll content elements\nPriority support\nMulti-language\n-Custom design", 'cta_label' => 'Choose Business', 'cta_link' => 't3://page?uid={page:contact}', 'featured' => 1, 'badge_text' => 'Most popular'],
                        ['name' => 'Enterprise', 'tagline' => 'For large organisations', 'currency' => '€', 'amount' => '199', 'period' => 'per month', 'price_note' => 'Billed annually', 'features' => "Unlimited pages\nAll content elements\nDedicated support\nMulti-language\nCustom design", 'cta_label' => 'Contact sales', 'cta_link' => 't3://page?uid={page:contact}'],
                    ]],
                    '_flexform' => ['sDEF' => ['settings.columns' => '3', 'settings.style' => 'shadow', 'settings.accentBar' => '1']],
                ],
                $logoWall,
                $faq,
                $cta('Not sure which plan is right?', 'Tell us about your project and we will find the best fit.'),
            ],
        ],
        [
            '_key' => 'contact',
            'title' => 'Contact',
            'description' => 'Contact Connetation Web Engineering GmbH in Salzburg — contact form, phone, email and address of the t3wtk team.',
            '_content' => [
                $intro('Contact', 'Let’s talk', 'Send us a message using the form below — it is the contact form shipped with connetation/t3wtk-sitepackage, including client-side validation and branded emails.'),
                [
                    'CType' => 'conn_grid_8_4',
                    'header' => 'Contact',
                    'header_layout' => '100',
                    '_children' => [
                        240 => [[
                            'CType' => 'form_formframework',
                            'header' => 'Write us',
                            'header_layout' => '2',
                            '_flexform' => ['sDEF' => [
                                'settings.persistenceIdentifier' => 'EXT:conn_t3wtk_sitepackage/Resources/Private/Forms/Contact.form.yaml',
                                'settings.overrideFinishers' => '0',
                            ]],
                        ]],
                        241 => [
                            $textIcon('telephone', 'Call us', '+43 662 216065<br>Mon–Fri, 9:00–17:00', 'circle', 'left'),
                            $textIcon('envelope', 'Email', 'office@connetation.at', 'circle', 'left'),
                            $textIcon('geo-alt', 'Visit', 'Vogelweiderstraße 44A<br>5020 Salzburg, Austria', 'circle', 'left'),
                        ],
                    ],
                ],
                [
                    'CType' => 'conn_map',
                    'header' => 'Find us',
                    'header_layout' => '3',
                    'space_before_class' => 'extra-large',
                    '_files' => ['tx_conn_t3wtk_elements_map_illustration_image' => ['map-connetation.png']],
                    '_flexform' => ['sDEF' => ['settings.provider' => 'static', 'settings.showGridOverImage' => '0', 'settings.height' => 'lg'], 'panel' => ['settings.panelPosition' => 'left']],
                ],
            ],
        ],
        [
            '_key' => 'imprint',
            'title' => 'Imprint',
            'description' => 'Imprint of the t3wtk demo site: Connetation Web Engineering GmbH, Vogelweiderstraße 44A, 5020 Salzburg, Austria.',
            'nav_hide' => 1,
            '_content' => [
                ['CType' => 'text', 'header' => 'Imprint', 'header_layout' => '1', 'bodytext' => $p(
                    '<strong>Connetation Web Engineering GmbH</strong><br>Vogelweiderstraße 44A<br>5020 Salzburg<br>Austria',
                    'Managing Director: Michael Gebhart',
                    'Phone: <a href="tel:+43662216065">+43 662 216065</a><br>Email: <a href="mailto:office@connetation.at">office@connetation.at</a>',
                    'Commercial register: Regional Court Salzburg, FN 394822x<br>VAT ID: ATU67860709',
                )],
                ['CType' => 'text', 'header' => 'Legal notice', 'header_layout' => '2', 'bodytext' => $p(
                    'All texts, images and other information published on this website are subject to the copyright of the provider, unless third-party copyrights apply. Reproduction, distribution or public display is only permitted with the revocable and non-transferable consent of the provider. Links to the content of this website require the consent of the provider. This consent must be requested again whenever the content of the linking website changes fundamentally, and it can be revoked informally at any time. Links from pages with illegal, offensive, misleading or defamatory content are excluded in every case. The content of this website is created with the greatest care. Nevertheless, no guarantee can be given that it is up to date and complete. The provider accepts no responsibility for web content reached via links, as it is not the provider\'s own content. External links were checked for unlawful content; none was apparent at the time of linking. There is, however, no general obligation to monitor and check third-party content. Trademarks and brand names mentioned are the property of their respective owners.',
                    'Despite careful control of the content, Connetation accepts no liability for content and offers reached via external links. The operators of the linked pages are solely responsible for their content. Graphics, videos, sounds and texts of this website may not be reproduced in other electronic or printed publications without express written consent.',
                )],
            ],
        ],
        [
            '_key' => 'privacy',
            'title' => 'Privacy Policy',
            'description' => 'Privacy policy of the t3wtk demo site: contact form data, no tracking, no third-party cookies.',
            'nav_hide' => 1,
            '_content' => [
                ['CType' => 'conn_text_column', 'header' => 'Privacy Policy', 'header_layout' => '1', 'bodytext' => $p('This is a placeholder privacy policy for the t3wtk demo site. Replace it with a policy that matches the data processing of your website.', 'The contact form sends the entered data by email to the site owner. No data is stored in the database by the demo form.', 'This demo site does not use tracking or third-party cookies. The maps show a static image instead of loading an external map provider.')],
            ],
        ],
        [
            '_key' => 'notfound',
            'title' => 'Page not found',
            'description' => 'The requested page of the t3wtk demo site could not be found.',
            'nav_hide' => 1,
            'no_search' => 1,
            '_content' => [
                array_replace($intro('404', 'This page does not exist', 'The page you are looking for may have been moved or deleted.'), [
                    'bodytext' => '<p class="lead text-center">The page you are looking for may have been moved or deleted.</p><p class="text-center">' . $button('Back to the homepage', 'home') . '</p>',
                ]),
            ],
        ],
        [
            '_key' => 'records',
            'title' => 'Demo Records',
            'doktype' => 254,
            '_records' => ['tx_conn_t3wtk_elements_domain_model_person' => [
                ['name' => 'Erika Musterfrau', 'role' => 'Managing Director', 'bio' => $p('Erika keeps the studio running and every project on track.'), 'email' => 'erika@example.com', '_files' => ['image' => ['square-1.jpg']]],
                ['name' => 'Max Mustermann', 'role' => 'Lead Developer', 'bio' => $p('Max turns designs into fast, accessible TYPO3 websites.'), 'email' => 'max@example.com', '_files' => ['image' => ['square-2.jpg']]],
                ['name' => 'Maria Muster', 'role' => 'Creative Director', 'bio' => $p('Maria shapes the look and feel of every brand we work with.'), 'email' => 'maria@example.com', '_files' => ['image' => ['square-3.jpg']]],
                ['name' => 'Hans Muster', 'role' => 'Content Strategist', 'bio' => $p('Hans makes sure every page has something worth reading.'), 'email' => 'hans@example.com', '_files' => ['image' => ['square-4.jpg']]],
            ]],
        ],
    ],
]];

// ── Site configuration + settings ──────────────────────────────────────────

return [
    'pages' => $pages,
    'site' => [
        // "base" is set by the installer: "/" on a fresh installation, this value next to existing sites.
        'baseWithOtherSites' => '/t3kit-demo/',
        'websiteTitle' => 't3wtk Demo',
        // Theme = second Set, from connetation/t3wtk-base: connetation/t3wtk-theme-{indigo|forest|sunset|midnight|flat}
        'dependencies' => ['connetation/t3wtk-demosite', 'connetation/t3wtk-theme-indigo'],
        'languages' => [[
            'title' => 'English',
            'enabled' => true,
            'languageId' => 0,
            'base' => '/',
            'locale' => 'en_US.UTF-8',
            'navigationTitle' => 'English',
            'flag' => 'en-us-gb',
            'websiteTitle' => '',
        ]],
        'errorHandling' => [[
            'errorCode' => 404,
            'errorHandler' => 'Page',
            'errorContentSource' => 't3://page?uid={page:notfound}',
        ]],
    ],
    // Site-specific values only. Colors, navbar and footer layout come from the connetation/t3wtk-base
    // theme Set — values here would override every theme. No branding.logo* here: the demo
    // logos (demosite.logo.*) are picked per theme by Theme/ThemeLogos, since a single logo
    // image can't suit light and dark navbars.
    'settings' => [
        'branding.logoAlt' => 'Connetation',
        'footer.copyrightName' => 'Connetation Web Engineering GmbH',
        'footer.tagline' => 'Beautiful TYPO3 websites, built with the TYPO3 Web Toolkit.',
        'footer.navigation.type' => 'list',
        'footer.navigation.pages' => '{page:features},{page:elements},{page:about},{page:pricing},{page:contact}',
        'footer.barNavigation.type' => 'list',
        'footer.barNavigation.pages' => '{page:imprint},{page:privacy}',
        'footer.barNavigation.includeNotInMenu' => true,
        // Contact (footer address, navbar panel, map element)
        'contact.address.company' => 'Connetation Web Engineering GmbH',
        'contact.address.street' => 'Vogelweiderstraße 44A',
        'contact.address.zip' => '5020',
        'contact.address.city' => 'Salzburg',
        'contact.address.country' => 'Austria',
        'contact.address.url' => 'https://maps.app.goo.gl/B375bNVhHqPtGyMT9',
        'contact.phone' => '+43 662 216065',
        'contact.email' => 'office@connetation.at',
        'contact.officeHours' => 'Mon–Fri, 9:00–17:00',
        'social.github' => 'https://github.com/connetation',
        'social.linkedin' => 'https://at.linkedin.com/company/connetation-web-engineering',
        'social.x' => 'https://x.com/connetation',
        'social.instagram' => 'https://www.instagram.com/connetation',
        'social.facebook' => 'https://www.facebook.com/connetation/',
    ],
];
