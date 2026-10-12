#!/usr/bin/env bash

#
# This file is part of the Connetation TYPO3 Web Toolkit - T3WTK.
#
# Copyright (c) Connetation Web Engineering GmbH
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#

# Regenerates the demo favicons in Resources/Public/Icons/Favicon (ImageMagick 6/7).
# The icon is the Connetation hexagon mark, the same artwork as the toolkit's extension
# icon (connetation/t3wtk-base Resources/Public/Icons/Extension.svg).
# Used by the branding.* settings in Configuration/Sets/Demosite/settings.yaml.
#   ddev exec vendor/connetation/t3wtk-demosite/Build/generate-favicons.sh
set -euo pipefail
cd "$(dirname "$0")/../Resources/Public/Icons/Favicon"

HEXAGON='M83.22,69.42v-26.03s0-4.51-3.9-6.75l-22.57-13.01s-3.9-2.24-7.81,0l-22.51,13.01s-3.9,2.24-3.9,6.75v26.06s0,4.51,3.9,6.75l22.54,13.04s3.9,2.27,7.81,0l22.51-13.01s3.93-2.3,3.93-6.81M42.73,55.2c0,2.27-1.85,4.12-4.11,4.12s-4.12-1.85-4.12-4.12,1.85-4.12,4.12-4.12c2.27.03,4.11,1.85,4.11,4.12M42.73,68.06c0,2.27-1.85,4.12-4.11,4.12s-4.12-1.85-4.12-4.12,1.85-4.12,4.12-4.12,4.11,1.84,4.11,4.12M59.28,40.86l-8.47,31.11-.27.21h-4.3l-.18-.21,8.47-31.11.27-.21h4.3l.18.21ZM71.3,40.86l-8.47,31.11-.27.21h-4.3l-.18-.21,8.47-31.11.27-.21h4.3l.18.21Z'
COLOR='#aaaaae'

# mark viewBox [background] — the hexagon (62×69, centered at 53/57) in a square viewBox
mark() {
    local viewbox=$1 bg=${2:-}
    echo "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"32\" height=\"32\" viewBox=\"$viewbox\">"
    [[ -n $bg ]] && echo "  <rect x=\"-100\" y=\"-100\" width=\"300\" height=\"300\" fill=\"$bg\"/>"
    echo "  <path fill=\"$COLOR\" d=\"$HEXAGON\"/>"
    echo "</svg>"
}

# Modern browsers: the mid-gray mark reads on light and dark tabs alike
mark "18.5 22.5 69 69" > connetation-favicon.svg

tmp=$(mktemp -d)
trap 'rm -rf "$tmp"' EXIT
mark "18.5 22.5 69 69" > "$tmp/tight.svg"
# Home-screen icons: opaque white square; iOS/Android apply their own mask, so the
# mark keeps to the maskable safe zone (central 80 %)
mark "-0.5 3.5 107 107" "#ffffff" > "$tmp/square.svg"

# size source output [convert options] — rendered large, then downscaled for clean edges
png() {
    convert -background none -density 1200 "$tmp/$2.svg" -resize "$1x$1" "${@:4}" -depth 8 -strip "$3"
}

# Legacy browsers, crawlers: multi-size ICO
for size in 16 32 48; do png $size tight "$tmp/ico-$size.png"; done
convert "$tmp/ico-16.png" "$tmp/ico-32.png" "$tmp/ico-48.png" connetation-favicon.ico

# Home-screen icons must be opaque
png 180 square connetation-apple-touch-icon.png -alpha off
png 192 square connetation-icon-192.png -alpha off
png 512 square connetation-icon-512.png -alpha off
