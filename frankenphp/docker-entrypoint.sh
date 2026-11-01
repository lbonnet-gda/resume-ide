#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ]; then
    composer install --prefer-dist --no-progress --no-interaction

    bin/console doctrine:migrations:migrate --no-interaction
fi

exec docker-php-entrypoint "$@"
