# t3kit Demo Site for TYPO3 LTS 14

Introduction package for the t3kit toolkit, in the spirit of TYPO3's
`typo3/cms-introduction`. Installing it creates a complete, good-looking
demo website: a page tree, a site configuration, site settings and content
elements from `conn/t3kit-elements`, all rendered with `conn/t3kit-base`
and `conn/t3kit-sitepackage`.

Use it to show clients what t3kit can do, to try out elements, or as a
reference for how each element is configured.

## Installation

```bash
composer require conn/t3kit-demosite
vendor/bin/typo3 extension:setup -e conn_t3kit_demosite
```

`conn/t3kit-sitepackage` must already be installed in the project. It is only
suggested in `composer.json`, not required, because every project owns its own
copy of it (e.g. `t3kit-quickstart-shell` moves it to `packages/` as a
`dev-local` path package, which a `^2.0.0` constraint would not accept). The
Site Set still declares `conn/t3kit-sitepackage` as a dependency.

`extension:setup` (also run by the TYPO3 installer and by activating the
extension in the Extension Manager) does the following once:

1. The core copies `Initialisation/Files/` to `fileadmin/conn_t3kit_demosite/`.
2. `InstallDemoSiteOnPackageInitialization` creates the page tree and content
   via DataHandler and writes `config/sites/t3kit-demosite/` (`config.yaml` +
   `settings.yaml`).

The installation is remembered in the registry, so it never runs twice. To
install again, delete the demo page tree and the `t3kit-demosite` site, then:

```bash
vendor/bin/typo3 t3kit:demosite:install --force
```

### Site base

- **Fresh installation (no other site):** the demo gets base `/`.
- **Next to existing sites:** the demo gets `/t3kit-demo/` on the same
  domain(s) as the first existing site, including its base variants.

Adjust `base` in Site Management afterwards if you want it somewhere else.

## Themes

The look comes from **conn/t3kit-base**'s theme Sets (colors light + dark,
navigation and footer layout, see its README). The demo site uses two Sets:
`conn/t3kit-demosite` for the content and functionality, plus one theme:

| Theme Set | Colors | Navigation | Footer |
|---|---|---|---|
| `conn/t3kit-theme-indigo` (installed default) | Indigo / violet | Horizontal, right aligned, grid dropdowns | Brand row, then 3 columns |
| `conn/t3kit-theme-forest` | Emerald / teal | Off-canvas panel from the right | Centered, dark green |
| `conn/t3kit-theme-sunset` | Rose / orange, warm paper | Horizontal, centered, list dropdowns | Minimal, one row |
| `conn/t3kit-theme-midnight` | Cyan / violet, dark navbar + footer | Off-canvas panel from the left | 3 columns, dark |

To switch, replace the theme in `config/sites/t3kit-demosite/config.yaml` (or in
Site Management → Sites → Sets) and flush the caches:

```yaml
dependencies:
  - conn/t3kit-demosite
  - conn/t3kit-theme-sunset
```

- **Order:** the order in the list doesn't matter; TYPO3 orders Sets by
  dependency.
- **Required:** keep `conn/t3kit-demosite` in the list. A theme alone only
  brings `conn/t3kit-base`, so the content elements would have no rendering
  definition.
- **Without a theme:** the site uses the conn/t3kit-base defaults.
- **`conn/t3kit-demosite` sets no theme keys** (only `themeToggle.enabled`),
  so every theme applies fully. The demo has no logo; the header and footer
  show the site title, which works on every theme.

**Keep theme values out of `config/sites/t3kit-demosite/settings.yaml`**
unless you mean to override the theme. Site settings override every Set, and
stay when you switch themes. The installer only writes site-specific data
there: contact details, social links and the footer menu page lists.

The demo content is theme-neutral on purpose. Content color fields only
store `#rrggbbaa` values and can't follow the theme, so bands use slate and
translucent gray. Buttons, icons, eyebrows, links and cards follow the theme.

## What gets created

| Page | Elements |
|---|---|
| **Home** (Top Content layout) | Hero Slider, Feature Grid, Stats (dark band), 2 Columns with Card Stack, Card Slider, Testimonials, Logo Wall (marquee), CTA |
| **Features** | 3 Columns with Text Icons, Feature Grid (horizontal), Process, Accordion (FAQ), CTA |
| **Elements** | Subpage Cards linking to the showcase pages below |
| ├ Text & Typography | Text & Media, Text (Columns), Quote, Text Icon, Alert (4 styles) |
| ├ Cards & Layout | Card Group, Feature Grid (steps), Narrow + Wide, 4 Columns, Card Stack |
| ├ Sliders | Parallax Slider, Bootstrap Carousel, Thumbnails Slider, Before/After |
| ├ Galleries | Mosaic Gallery, Flip Gallery, Flip Carousel, Logo Wall (grid) |
| ├ Interactive | Tabs, Accordion, Process, Timeline |
| └ Data & People | Stats, People, Testimonials (carousel), Map |
| **About** | Wide + Narrow, People (reveal), Stats, Timeline, CTA |
| **Pricing** | Pricing Table, Logo Wall, FAQ, CTA |
| **Contact** | Contact form (from `conn/t3kit-sitepackage`) + Text Icons, Map |
| Imprint, Privacy Policy | Hidden in menu, linked in the footer bar |
| Page not found | 404 error page of the site |
| Demo Records (folder) | Person records for the People element |

Everything is regular TYPO3 content: edit or delete it like any other page.

Not included: the Video Hero element (it needs a video file or YouTube/Vimeo
media, which can't be shipped offline), news and events.

## Changing the demo content

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
        '_inline' => ['tx_conn_t3kit_elements_card_group_items' => [   // IRRE children
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
ddev exec packages/t3kit-demosite/Build/generate-images.sh
```

## Structure

```
conn/t3kit-demosite/                   # ext key: conn_t3kit_demosite
├── Build/generate-images.sh           # Regenerates the demo images (ImageMagick)
├── Classes/
│   ├── Command/InstallDemoSiteCommand.php                 # t3kit:demosite:install
│   ├── EventListener/InstallDemoSiteOnPackageInitialization.php
│   └── Installer/DemoSiteInstaller.php                    # DataHandler-based importer
├── Configuration/
│   ├── DemoSite/Definition.php        # The demo: pages, content, site config, settings
│   └── Sets/
│       └── Demosite/                  # conn/t3kit-demosite: dependencies, form redirect override
└── Initialisation/Files/images/       # Copied to fileadmin/conn_t3kit_demosite/
```
