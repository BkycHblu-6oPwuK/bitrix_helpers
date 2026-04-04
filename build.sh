#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
NUXT_DIR="$SCRIPT_DIR/nuxt"

MODE="${1:-auto}"

log() {
	echo "[build.sh] $*"
}

fail() {
	echo "[build.sh] ERROR: $*" >&2
	exit 1
}

need_cmd() {
	command -v "$1" >/dev/null 2>&1 || fail "Command not found: $1"
}

run() {
	log "$*"
	"$@"
}

case "$MODE" in
	auto|local|prod)
		;;
	*)
		fail "Unknown mode: $MODE. Use: auto | local | prod"
		;;
esac

if [[ "$MODE" == "auto" ]]; then
	if command -v docky >/dev/null 2>&1; then
		MODE="local"
	else
		MODE="prod"
	fi
fi

log "Mode: $MODE"

if [[ ! -f "$SCRIPT_DIR/composer.json" ]]; then
	fail "composer.json not found in $SCRIPT_DIR"
fi

if [[ ! -f "$NUXT_DIR/package.json" ]]; then
	fail "package.json not found in $NUXT_DIR"
fi

if [[ "$MODE" == "local" ]]; then
	need_cmd docky

	run docky composer install --no-interaction --prefer-dist

	run docky npm install
	run docky npm run build
	run docky pm2 restart nuxt-ssr
else
	need_cmd composer
	need_cmd npm
	need_cmd pm2

	cd "$SCRIPT_DIR"
	run composer install --no-interaction --prefer-dist

	cd "$NUXT_DIR"
	run npm install
	run npm run build

	run pm2 restart nuxt-ssr
fi

log "Done"
