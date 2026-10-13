# T3WTK – Connetation TYPO3 Web Toolkit

From an empty TYPO3 installation to a complete, themeable website in hours instead of weeks.

T3WTK is a modular set of TYPO3 extensions for TYPO3 14 LTS. It replaces the groundwork
that has to be redone on almost every project: sitepackage, page templates, navigation,
footer, content elements, dark mode, forms and SEO setup. Once installed via Composer,
your team can start with configuration, design and content right away.

## Packages

| Package | Purpose |
|---|---|
| `connetation/t3wtk-base` | Design system & foundation: page templates, navigation, footer, design tokens, light/dark theme, site settings, default theme |
| `connetation/t3wtk-elements` | 30+ content elements in eight categories |
| `connetation/t3wtk-sitepackage` | Project layer: theme overrides, forms, email templates, SEO configuration |
| `connetation/t3wtk-demosite` | Creates a complete demo page tree with sample content |
| `connetation/t3wtk-themes` | Theme library (in development) |

Project-specific code lives only in the sitepackage, so base and elements can be updated
via Composer like any other dependency without overwriting your customizations.

## Highlights

- **Design once, theme everywhere:** colors, logo, navbar, footer, buttons and border radius
  as TYPO3 site settings. 250+ options, editable in the Sites module.
- **Full dark mode** with its own color palette and theme toggle, per site, no extra CSS
- **30+ content elements:** hero and card sliders, video hero, before/after comparison,
  mosaic and lightbox galleries, accordion, tabs, flip cards, timeline, pricing tables,
  stats/counter, testimonials, team, map & location and more
- **Five container layouts** (2/3/4 columns, narrow/wide, wide/narrow), nestable
- **Shared design options for every element:** spacing, content width up to full-bleed,
  backgrounds (color, image, parallax), scroll and text animations
- **Built on TYPO3 standards:** Site Sets, Fluid, Composer, core "Text & Media" as a base,
  regular TYPO3 content records with no proprietary structures
- **Performance:** responsive images, lazy loading, CSS/JS loaded only for elements actually
  used on a page
- **Accessibility-minded markup:** Bootstrap 5, semantic HTML, clean heading hierarchy,
  keyboard-operable widgets
- **Included:** contact form (Form Framework) with branded email templates, SEO setup via
  cs_seo (Open Graph, Twitter Cards, structured data), favicon/branding set, three icon
  libraries (Bootstrap Icons, Font Awesome, Ionicons)

## Requirements

- TYPO3 14 LTS (tested on 14.3), Composer mode
- Uses `b13/container` and `clickstorm/cs_seo`

## Quick start

1. `composer require connetation/t3wtk-base connetation/t3wtk-elements`
2. Add the T3WTK site set to your site configuration
3. Configure colors, logo, navigation and footer in the site settings
4. Build pages with the ready-made content elements

Want to explore first? Install `connetation/t3wtk-demosite` or visit https://demosite.connetation.at

## Feedback & contributing

T3WTK is a young open-source project. Bug reports, ideas and pull requests are welcome via
GitHub Issues and Discussions.

---

## This package: `connetation/t3wtk-demosite`

Demo site for the Connetation TYPO3 Web Toolkit (T3WTK). Automatically creates a complete page tree with site configuration, sample content and six color themes. Ideal for evaluation and as a reference.

Introduction package for the TYPO3 Web Toolkit (t3wtk), in the spirit of TYPO3's
`typo3/cms-introduction`. Installing it creates a complete, good-looking
demo website: a page tree, a site configuration, site settings and content
elements from `connetation/t3wtk-elements`, all rendered with `connetation/t3wtk-base`
and `connetation/t3wtk-sitepackage`.

Use it to show clients what the TYPO3 Web Toolkit can do, to try out elements, or as a
reference for how each element is configured.

### Installation

```bash
composer require connetation/t3wtk-demosite
vendor/bin/typo3 extension:setup -e conn_t3wtk_demosite
```

`connetation/t3wtk-sitepackage` must already be installed in the project. It is only
suggested in `composer.json`, not required, because every project owns its own
copy of it (e.g. `t3kit-quickstart-shell` moves it to `packages/` as a
`dev-local` path package, which a `^2.0.0` constraint would not accept). The
Site Set still declares `connetation/t3wtk-sitepackage` as a dependency.

`extension:setup` (also run by the TYPO3 installer and by activating the
extension in the Extension Manager) does the following once:

1. The core copies `Initialisation/Files/` to `fileadmin/conn_t3wtk_demosite/`.
2. `InstallDemoSiteOnPackageInitialization` creates the page tree and content
   via DataHandler and writes `config/sites/t3kit-demosite/` (`config.yaml` +
   `settings.yaml`).

The installation is remembered in the registry, so it never runs twice. To
install again, delete the demo page tree and the `t3kit-demosite` site, then:

```bash
vendor/bin/typo3 t3wtk:demosite:install --force
```

#### Site base

- **Fresh installation (no other site):** the demo gets base `/`.
- **Next to existing sites:** the demo gets `/t3kit-demo/` on the same
  domain(s) as the first existing site, including its base variants.

Adjust `base` in Site Management afterwards if you want it somewhere else.

### Themes

The look comes from **connetation/t3wtk-base**'s theme Sets (colors light + dark,
navigation and footer layout, see its README). The demo site uses two Sets:
`connetation/t3wtk-demosite` for the content and functionality, plus one theme:

| Theme Set | Colors | Navigation | Footer |
|---|---|---|---|
| `connetation/t3wtk-theme-indigo` (installed default) | Indigo / violet | Horizontal, right aligned, grid dropdowns | Brand row, then 3 columns |
| `connetation/t3wtk-theme-forest` | Emerald / teal | Off-canvas panel from the right | Centered, dark green |
| `connetation/t3wtk-theme-sunset` | Rose / orange, warm paper | Horizontal, centered, list dropdowns | Minimal, one row |
| `connetation/t3wtk-theme-midnight` | Cyan / violet, dark navbar + footer | Off-canvas panel from the left | 3 columns, dark |
| `connetation/t3wtk-theme-flat` | Zinc / blue, flat: square corners, no shadows | Horizontal, right aligned, list dropdowns | 4 columns, dark |

To switch, replace the theme in `config/sites/t3kit-demosite/config.yaml` (or in
Site Management → Sites → Sets) and flush the caches:

```yaml
dependencies:
  - connetation/t3wtk-demosite
  - connetation/t3wtk-theme-sunset
```

- **Order:** the order in the list doesn't matter; TYPO3 orders Sets by
  dependency.
- **Required:** keep `connetation/t3wtk-demosite` in the list. A theme alone only
  brings `connetation/t3wtk-base`, so the content elements would have no rendering
  definition.
- **Without a theme:** the site uses the connetation/t3wtk-base defaults.
- **`connetation/t3wtk-demosite` sets no theme keys** (only `themeToggle.enabled`
  and the `branding.*` favicons), so every theme applies fully. The demo has
  no logo; the header and footer show the site title, which works on every
  theme.

**Keep theme values out of `config/sites/t3kit-demosite/settings.yaml`**
unless you mean to override the theme. Site settings override every Set, and
stay when you switch themes. The installer only writes site-specific data
there: contact details, social links and the footer menu page lists.

The demo content is theme-neutral on purpose. Content color fields only
store `#rrggbbaa` values and can't follow the theme, so bands use slate and
translucent gray. Buttons, icons, eyebrows, links and cards follow the theme.

### What gets created

| Page | Elements |
|---|---|
| **Home** (Top Content layout) | Hero Slider, Feature Grid, Stats (dark band), 2 Columns with Card Stack, Card Slider, Testimonials, Logo Wall (marquee), CTA |
| **Features** | 3 Columns with Text Icons, Feature Grid (horizontal), Process, Accordion (FAQ), CTA |
| **Elements** | Subpage Cards linking to the showcase pages below |
| ├ Text & Typography | Text & Media, Text (Columns), Quote, Text Icon, Alert (4 styles) |
| ├ Cards & Layout | Card Group, Feature Grid (steps), Narrow + Wide, 4 Columns, Card Stack |
| ├ Sliders | Parallax Slider, Bootstrap Carousel, Thumbnails Slider, Before/After |
| ├ Galleries | Mosaic Gallery, Lightbox Gallery, Shift Carousel, Logo Wall (grid) |
| ├ Interactive | Tabs, Accordion, Process, Timeline |
| └ Data & People | Stats, People, Testimonials (carousel), Map |
| **About** | Wide + Narrow, People (reveal), Stats, Timeline, CTA |
| **Pricing** | Pricing Table, Logo Wall, FAQ, CTA |
| **Contact** | Contact form (from `connetation/t3wtk-sitepackage`) + Text Icons, Map |
| Imprint, Privacy Policy | Hidden in menu, linked in the footer bar |
| Page not found | 404 error page of the site |
| Demo Records (folder) | Person records for the People element |

Everything is regular TYPO3 content: edit or delete it like any other page.

Not included: the Video Hero element (it needs a video file or YouTube/Vimeo
media, which can't be shipped offline), news and events.

### Changing the demo content

Everything is declared in **`Configuration/DemoSite/Definition.php`**: pages,
their content, site configuration and site settings. The format is documented
in `Classes/Installer/DemoSiteInstaller.php`. In short:

```php
[
    '_key' => 'about',                       // page key, referenced as {page:about}
    'title' => 'About',                      // any pages field
    '_content' => [[
        'CType' => 'conn_bs_card_group',     // any tt_content field
        'header' => 'Cards',
        '_inline' => ['tx_conn_t3wtk_elements_card_group_items' => [   // IRRE children
            ['title' => 'Card', '_files' => ['image' => ['card-teal.jpg']]],
        ]],
        '_flexform' => ['sDEF' => ['settings.columns' => '3']],
        // containers: '_children' => [200 => [ce, …], 201 => [ce, …]]
    ]],
    '_subpages' => [ … ],
]
```

Images are referenced by file name from `Initialisation/Files/images/`. They
are generated abstract gradients (no licensing issues); regenerate or add new
ones with:

```bash
ddev exec vendor/connetation/t3wtk-demosite/Build/generate-images.sh
```

The favicons in `Resources/Public/Icons/Favicon/` (SVG, ICO, apple-touch-icon
and the 192/512 manifest icons) show the Connetation hexagon mark, the same
artwork as the toolkit's extension icon. The Set wires them up via the `branding.*` settings; regenerate them with:

```bash
ddev exec vendor/connetation/t3wtk-demosite/Build/generate-favicons.sh
```

### Structure

```
connetation/t3wtk-demosite/                   # ext key: conn_t3wtk_demosite
├── Build/generate-images.sh           # Regenerates the demo images (ImageMagick)
├── Build/generate-favicons.sh         # Regenerates the favicons (ImageMagick)
├── Classes/
│   ├── Command/InstallDemoSiteCommand.php                 # t3wtk:demosite:install (alias: t3kit:demosite:install)
│   ├── EventListener/InstallDemoSiteOnPackageInitialization.php
│   └── Installer/DemoSiteInstaller.php                    # DataHandler-based importer
├── Configuration/
│   ├── DemoSite/Definition.php        # The demo: pages, content, site config, settings
│   └── Sets/
│       └── Demosite/                  # connetation/t3wtk-demosite: dependencies, form redirect override
├── Initialisation/Files/images/       # Copied to fileadmin/conn_t3wtk_demosite/
└── Resources/Public/Icons/Favicon/    # Demo favicons (branding.* settings)
```

## License

GPL-2.0-or-later · Developed by Connetation Web Engineering GmbH, Salzburg, Austria
