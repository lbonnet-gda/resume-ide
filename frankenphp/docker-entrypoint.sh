#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] && [ -z "$(ls -A vendor/ 2>/dev/null)" ]; then
    composer install --prefer-dist --no-progress --no-interaction
fi

exec docker-php-entrypoint "$@"
