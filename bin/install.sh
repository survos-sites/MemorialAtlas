#!/usr/bin/env bash
# Works both from a checkout and as a complete script piped to bash.
set -Eeuo pipefail

main() {
    local target='' existing=0 source_file="${BASH_SOURCE[0]:-}" managed=0
    while (($#)); do
        case "$1" in
            --dir) [[ $# -ge 2 ]] || { echo '--dir requires a directory.' >&2; return 1; }; target=$2; shift 2 ;;
            --existing-services) existing=1; shift ;;
            --help|-h)
                echo 'Usage: bash bin/install.sh [--dir DIRECTORY] [--existing-services]'
                echo 'Default: clone MemorialAtlas and start its local database, search and thumbnails.'
                echo 'Existing .env.local files are preserved. Use --existing-services with your own configured services.'
                return ;;
            *) echo "Unknown option: $1" >&2; return 1 ;;
        esac
    done
    local command
    for command in git php composer symfony; do
        command -v "$command" >/dev/null || { echo "Install $command first, then rerun this installer. See https://github.com/survos-sites/MemorialAtlas#setup" >&2; return 1; }
    done
    php -r '
        $missing = array_filter(["ctype", "gd", "iconv", "pdo_pgsql", "pdo_sqlite"], fn($e) => !extension_loaded($e));
        if (PHP_VERSION_ID < 80500 || $missing) {
            fwrite(STDERR, "Requires PHP 8.5+ with ctype, GD, iconv, PDO PostgreSQL and SQLite. Missing: ".implode(", ", $missing).PHP_EOL);
            exit(1);
        }
    '
    if [[ -z "$target" ]]; then
        if [[ -n "$source_file" && -f "$source_file" && -f "$(dirname "$source_file")/../composer.json" ]]; then
            target=$(cd "$(dirname "$source_file")/.." && pwd)
        else
            target="$PWD/MemorialAtlas"
        fi
    fi
    if [[ ! -e "$target" ]]; then
        git clone --depth 1 --branch main https://github.com/survos-sites/MemorialAtlas.git "$target"
    fi
    [[ -f "$target/bin/load-data" && -f "$target/docker-compose.yaml" ]] || { echo "Not a MemorialAtlas checkout: $target" >&2; return 1; }
    cd "$target"
    if [[ ! -f .env.local ]]; then
        if ((existing)); then
            echo 'Create .env.local with DATABASE_URL, ELASTICSEARCH_DSN and UX_IMAGE_DSN for your services, then rerun with --existing-services.' >&2
            return 1
        fi
        command -v docker >/dev/null || { echo 'Install Docker with Compose, or configure existing services in .env.local.' >&2; return 1; }
        docker compose version >/dev/null
        docker info >/dev/null 2>&1 || { echo 'Start Docker, then rerun this installer.' >&2; return 1; }
        local secret
        secret=$(php -r 'echo bin2hex(random_bytes(32));')
        (umask 077; printf '%s\n' \
            '# MemorialAtlas installer: managed local services' \
            "APP_SECRET=$secret" \
            'DATABASE_URL="postgresql://app:app@127.0.0.1:5544/memorialatlas?serverVersion=18&charset=utf8"' \
            'ELASTICSEARCH_DSN=elasticsearch://127.0.0.1:19200' \
            'UX_IMAGE_DSN="imagor://127.0.0.1:8088?secure=false"' > .env.local)
    fi
    if (( !existing )) && grep -q '^# MemorialAtlas installer: managed local services$' .env.local; then
        managed=1
    fi
    composer install --no-interaction --prefer-dist
    if ((managed)); then
        mkdir -p var/media var/imagor var/postgres
        docker compose --profile standalone --profile search up -d --wait --wait-timeout 180
    else
        echo 'Using existing .env.local and services; no database container will be started.'
    fi
    bin/load-data
    php bin/console elastic:index:rebuild --no-interaction
    php bin/console app:processing:status
    printf '\nMemorialAtlas is ready in %s. Open the address printed by Symfony.\n' "$PWD"
    echo 'The saved demo needs no API keys. Processing configuration belongs in .env.local; see README.md.'
    symfony server:start -d
}

# Parse the whole script before executing commands that might read stdin.
main "$@" </dev/null
