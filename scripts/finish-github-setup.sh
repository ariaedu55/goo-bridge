#!/bin/zsh
# Installe .git dans le projet et pousse vers GitHub (compte ariaedu55).
set -euo pipefail
cd "$(dirname "$0")/.."
STORE="${GIT_STORE:-/tmp/goo-bridge-git-2564}"

if [[ ! -d "$STORE" ]]; then
  echo "Dépôt temporaire introuvable: $STORE"
  echo "Relance depuis Cursor ou fais: git init && git remote add origin https://github.com/ariaedu55/goo-bridge.git"
  exit 1
fi

if [[ -f .git ]]; then
  rm -f .git
fi
if [[ -d .git ]]; then
  echo "Un dossier .git existe déjà — abandon."
  exit 1
fi

cp -a "$STORE" .git
echo "✓ .git installé dans le projet"

echo "Connexion GitHub (choisis HTTPS, compte ariaedu55)…"
gh auth login

git remote set-url origin https://github.com/ariaedu55/goo-bridge.git
git push -u origin main
echo "✓ Push terminé: https://github.com/ariaedu55/goo-bridge"
