#!/usr/bin/env bash
set -euo pipefail

VERSION="${1:-dev}"
DATE=$(date +%Y%m%d)
SHA=$(git rev-parse --short HEAD 2>/dev/null || echo "local")

if [ "$VERSION" = "nightly" ]; then
  TARBALL_NAME="binglogy-nightly-${DATE}-${SHA}.tar.gz"
  TOPDIR="binglogy-nightly-${DATE}-${SHA}"
else
  TARBALL_NAME="binglogy-v${VERSION}.tar.gz"
  TOPDIR="binglogy-v${VERSION}"
fi

mkdir -p dist

tar -czf "dist/${TARBALL_NAME}" \
  --exclude='.git' \
  --exclude='.github' \
  --exclude='.idea' --exclude='.vscode' --exclude='.zed' --exclude='.fleet' --exclude='.nova' \
  --exclude='.agent' --exclude='.codewhale' --exclude='.vite-hooks' \
  --exclude='node_modules' \
  --exclude='tests' \
  --exclude='plans' \
  --exclude='.env' --exclude='.env.backup' --exclude='.env.production' --exclude='.env.local' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='storage/framework/testing/*' \
  --exclude='storage/pail' \
  --exclude='*.key' \
  --exclude='auth.json' \
  --exclude='Homestead.json' --exclude='Homestead.yaml' \
  --exclude='phpunit.xml' \
  --exclude='pint.json' \
  --exclude='.phpunit.cache' --exclude='.phpunit.result.cache' \
  --exclude='package-lock.json' \
  --exclude='frankenphp' \
  --exclude='frankenphp-worker.php' \
  --exclude='**/caddy' \
  --exclude='composer.phar' \
  --transform "s,^,${TOPDIR}/," \
  .

SIZE=$(du -h "dist/${TARBALL_NAME}" | cut -f1)
echo "::notice title=tarball::Built dist/${TARBALL_NAME} (${SIZE})"
