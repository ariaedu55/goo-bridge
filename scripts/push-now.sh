#!/bin/zsh
# À lancer dans Terminal.app (hors Cursor) avec le compte ariaedu55.
set -euo pipefail
cd /Applications/XAMPP/xamppfiles/htdocs/bridge

STORE=/tmp/goo-bridge-git-2564
if [[ ! -d "$STORE" ]]; then
  echo "Dépôt temporaire introuvable. Réinitialisation…"
  git init
  git branch -M main
  git remote add origin https://github.com/ariaedu55/goo-bridge.git 2>/dev/null || true
  git add -A
  git commit -m "Initial commit — site Goo-Bridge (agence digitale)." || true
else
  export GIT_DIR="$STORE"
  export GIT_WORK_TREE=/Applications/XAMPP/xamppfiles/htdocs/bridge
fi

echo "Connexion GitHub (compte ariaedu55)…"
gh auth login -h github.com -p https -w
gh auth setup-git

git remote set-url origin https://github.com/ariaedu55/goo-bridge.git
git push -u origin main
echo "OK → https://github.com/ariaedu55/goo-bridge"
