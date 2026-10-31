FROM dunglas/frankenphp:1-php8.4-trixie

ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Same uid/gid as the host user, so files written to the bind-mounted project are not owned by root.
# The frankenphp binary ships with cap_net_bind_service, so this user can still bind ports 80 and 443.
RUN groupadd --gid "$GID" app \
    && useradd --uid "$UID" --gid "$GID" --create-home app \
    && chown -R app:app /data /config

COPY --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

USER app

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
