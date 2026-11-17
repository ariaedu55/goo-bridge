#!/bin/bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CHROME="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
MOBILE="$ROOT/images/realisations/mobile-screens"
OUT="$ROOT/images/realisations/phones"
mkdir -p "$MOBILE" "$OUT"

capture() {
  local slug="$1"
  local url="$2"
  local shot="$MOBILE/${slug}.png"
  echo "Capture $slug …"
  "$CHROME" --headless=new --disable-gpu \
    --screenshot="$shot" \
    --window-size=440,956 \
    --hide-scrollbars \
    --virtual-time-budget=10000 \
    "$url" 2>/dev/null || true
  if [[ ! -s "$shot" ]]; then
    echo "  fallback local image for $slug"
    case "$slug" in
      sugar-paper) cp "$ROOT/images/realisation-sugar-paper.webp" "$MOBILE/${slug}.webp" ; shot="$MOBILE/${slug}.webp" ;;
      aria-edu) cp "$ROOT/images/realisation-aria-edu.webp" "$MOBILE/${slug}.webp" ; shot="$MOBILE/${slug}.webp" ;;
      fouta-poids) cp "$ROOT/images/realisation-fouta-poids.webp" "$MOBILE/${slug}.webp" ; shot="$MOBILE/${slug}.webp" ;;
      aria-education) cp "$ROOT/images/realisation-aria-education.webp" "$MOBILE/${slug}.webp" ; shot="$MOBILE/${slug}.webp" ;;
      colobanes) cp "$ROOT/images/realisation-colobanes.webp" "$MOBILE/${slug}.webp" ; shot="$MOBILE/${slug}.webp" ;;
      tresor-africain) cp "$ROOT/images/realisation-tresorafricain.webp" "$MOBILE/${slug}.webp" ; shot="$MOBILE/${slug}.webp" ;;
    esac
  fi
  php "$ROOT/scripts/build-phone-mockups.php" "$shot" "$OUT/phone-${slug}.webp"
}

capture "sugar-paper" "https://sugar-paper.com/"
capture "aria-edu" "https://aria-edu.com/"
capture "fouta-poids" "https://e.foutapoidslourds.com/"
capture "aria-education" "https://aria-education.com/"
capture "colobanes" "https://colobanes.com/"
capture "tresor-africain" "https://tresorafricain.com/"

echo "Terminé — mockups dans $OUT"
