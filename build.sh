#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
NUXT_DIR="$SCRIPT_DIR/nuxt"
MODE="auto"
PM2_CONFIG="ecosystem.config.cjs"

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

if [[ "$MODE" == "auto" ]]; then
	if command -v docky >/dev/null 2>&1; then
		MODE="local"
	else
		MODE="prod"
	fi
fi

while [[ $# -gt 0 ]]; do
	case "$1" in
		--mode=*)
			MODE="${1#*=}"
			shift
			;;
		--mode)
			MODE="$2"
			shift 2
			;;
		--pm2=*)
			PM2_CONFIG="${1#*=}"
			shift
			;;
		--pm2)
			PM2_CONFIG="$2"
			shift 2
			;;
		*)
			fail "Unknown argument: $1"
			;;
	esac
done

case "$MODE" in
	local|prod)
		;;
	*)
		fail "Unknown mode: $MODE. Use: local | prod"
		;;
esac

log "Mode: $MODE"
log "PM2 config: $PM2_CONFIG"

[[ -f "$NUXT_DIR/$PM2_CONFIG" ]] || fail "PM2 config not found: $PM2_CONFIG"
[[ -f "$SCRIPT_DIR/composer.json" ]] || fail "composer.json not found"
[[ -f "$NUXT_DIR/package.json" ]] || fail "package.json not found"

if [[ "$MODE" == "local" ]]; then
	need_cmd docky

	run docky composer install --no-interaction --prefer-dist

	run docky npm install
	run docky npm run build

	run docky pm2 startOrRestart "$PM2_CONFIG" --update-env
else
	need_cmd composer
	need_cmd npm
	need_cmd pm2

	cd "$SCRIPT_DIR"
	run composer install --no-interaction --prefer-dist

	cd "$NUXT_DIR"
	run npm install
	run npm run build

	run pm2 startOrRestart "$PM2_CONFIG" --update-env
fi

log "Done"