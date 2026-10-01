#!/usr/bin/env bash
# Regenerates the demo favicons in Resources/Public/Icons/Favicon (ImageMagick 6/7).
# The icon is the "T" mark of the demo logo (Initialisation/Files/images/t3kit-logo-*.svg).
# Used by the branding.* settings in Configuration/Sets/Demosite/settings.yaml.
#   ddev exec packages/t3kit-demosite/Build/generate-favicons.sh
set -euo pipefail
cd "$(dirname "$0")/../Resources/Public/Icons/Favicon"

# mark  radius bg fg dot — the logo mark on a 32×32 grid
mark() {
    local radius=$1 bg=$2 fg=$3 dot=$4
    cat <<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
  <rect width="32" height="32" rx="$radius" fill="$bg"/>
  <path d="M9 9h14M16 9v15" fill="none" stroke="$fg" stroke-width="3.5" stroke-linecap="round"/>
  <circle cx="24.5" cy="23" r="2.5" fill="$dot"/>
</svg>
SVG
}

# Modern browsers: SVG that switches to the light-logo colors in dark mode
cat > favicon.svg <<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
  <style>
    .bg { fill: #0f172a } .fg { stroke: #ffffff } .dot { fill: #94a3b8 }
    @media (prefers-color-scheme: dark) {
      .bg { fill: #f8fafc } .fg { stroke: #0f172a } .dot { fill: #64748b }
    }
  </style>
  <rect class="bg" width="32" height="32" rx="8"/>
  <path class="fg" d="M9 9h14M16 9v15" fill="none" stroke-width="3.5" stroke-linecap="round"/>
  <circle class="dot" cx="24.5" cy="23" r="2.5"/>
</svg>
SVG

tmp=$(mktemp -d)
trap 'rm -rf "$tmp"' EXIT
mark 8 "#0f172a" "#ffffff" "#94a3b8" > "$tmp/rounded.svg"
# Full-bleed square for home-screen icons: iOS/Android apply their own mask, and the
# mark stays inside the maskable safe zone
mark 0 "#0f172a" "#ffffff" "#94a3b8" > "$tmp/square.svg"

# size source output [convert options] — rendered large, then downscaled for clean edges
png() {
    convert -background none -density 1200 "$tmp/$2.svg" -resize "$1x$1" "${@:4}" -depth 8 -strip "$3"
}

# Legacy browsers, crawlers: multi-size ICO
for size in 16 32 48; do png $size rounded "$tmp/ico-$size.png"; done
convert "$tmp/ico-16.png" "$tmp/ico-32.png" "$tmp/ico-48.png" favicon.ico

# Home-screen icons must be opaque
png 180 square apple-touch-icon.png -alpha off
png 192 square icon-192.png -alpha off
png 512 square icon-512.png -alpha off
