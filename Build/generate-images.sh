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

# Regenerates the abstract demo images in Initialisation/Files/images (ImageMagick 6/7).
# Deterministic: every image uses a fixed seed, so re-running produces the same files.
#   ddev exec packages/t3kit-demosite/Build/generate-images.sh
set -euo pipefail
cd "$(dirname "$0")/../Initialisation/Files/images"

# name  width height seed colorA colorB [colorC]
# Grayscale plasma, blurred, then mapped onto an A→B(→C) gradient: soft "mesh gradient"
# images that only contain the given palette colors.
mesh() {
    local name=$1 w=$2 h=$3 seed=$4 a=$5 b=$6 c=${7:-$6}
    convert -seed "$seed" -size $((w / 4))x$((h / 4)) plasma:fractal -colorspace Gray \
        -resize "${w}x${h}!" -blur 0x$((w / 40)) -auto-level \
        \( -size 1x256 gradient:"$a"-"$b" \( -size 1x256 gradient:"$b"-"$c" \) -append -resize 1x256! \) -clut \
        \( -size "${w}x${h}" radial-gradient:"rgba(255,255,255,0.18)"-"rgba(0,0,0,0)" \) \
        -compose screen -composite -strip -interlace Plane -quality 80 "$name.jpg"
}

# Hero / section backgrounds (16:9)
mesh hero-indigo   1920 1080 7  "#1e1b4b" "#4f46e5" "#c084fc"
mesh hero-ocean    1920 1080 21 "#082f49" "#0e7490" "#5eead4"
mesh hero-sunset   1920 1080 42 "#4c0519" "#db2777" "#fdba74"
mesh hero-midnight 1920 1080 5  "#020617" "#1e293b" "#475569"

# Cards / media (3:2)
mesh card-violet  1200 800 11 "#2e1065" "#7c3aed" "#ddd6fe"
mesh card-teal    1200 800 12 "#042f2e" "#0d9488" "#99f6e4"
mesh card-amber   1200 800 13 "#451a03" "#d97706" "#fde68a"
mesh card-rose    1200 800 14 "#4c0519" "#e11d48" "#fecdd3"
mesh card-sky     1200 800 15 "#082f49" "#0284c7" "#bae6fd"
mesh card-emerald 1200 800 16 "#022c22" "#059669" "#a7f3d0"

# Squares (avatars, gallery)
mesh square-1 800 800 31 "#1e1b4b" "#6366f1" "#c7d2fe"
mesh square-2 800 800 32 "#042f2e" "#14b8a6" "#ccfbf1"
mesh square-3 800 800 33 "#431407" "#ea580c" "#fed7aa"
mesh square-4 800 800 34 "#500724" "#db2777" "#fbcfe8"

# Before/After pair: desaturated + dimmed "before", full color "after"
mesh compare-after 1600 900 58 "#0c4a6e" "#0891b2" "#fcd34d"
convert compare-after.jpg -modulate 85,0 -strip -quality 80 compare-before.jpg

# Fictional brand logos for the Logo Wall (plain SVG wordmarks, German placeholder names —
# no real companies)
logo() {
    local name=$1 label=$2 color=$3 shape=$4
    cat > "logo-$name.svg" <<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60" viewBox="0 0 220 60" role="img" aria-label="$label">
  $shape
  <text x="58" y="38" font-family="Inter, 'Segoe UI', Helvetica, Arial, sans-serif" font-size="22" font-weight="700" fill="$color">$label</text>
</svg>
SVG
}
logo musterfirma Musterfirma    "#0f766e" '<path d="M14 44V16l24 28V16" fill="none" stroke="#0f766e" stroke-width="5" stroke-linejoin="round"/>'
logo beispiel    "Beispiel AG"  "#b45309" '<circle cx="28" cy="30" r="14" fill="#f59e0b"/><circle cx="28" cy="30" r="6" fill="#fff"/>'
logo probewerk   Probewerk      "#4338ca" '<path d="M12 18h32L28 46z" fill="#6366f1"/>'
logo testhaus    Testhaus       "#be185d" '<circle cx="28" cy="30" r="15" fill="none" stroke="#db2777" stroke-width="4"/><circle cx="40" cy="20" r="5" fill="#db2777"/>'
logo vorlage     "Vorlage &amp; Co" "#0369a1" '<path d="M28 12l14 18-14 18-14-18z" fill="#0ea5e9"/>'
logo platzhalter Platzhalter    "#475569" '<path d="M16 40a9 9 0 0 1 2-18 12 12 0 0 1 22 3 8 8 0 0 1 0 15z" fill="#94a3b8"/>'
