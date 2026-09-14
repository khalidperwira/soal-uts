# =============================================================
#  Backend Ujian Praktik - Laravel + FrankenPHP
#  Build:  docker compose build
# =============================================================

# ---------- Stage 1: dependency Composer ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
      --no-dev \
      --no-scripts \
      --no-autoloader \
      --prefer-dist \
      --no-interaction \
      --no-progress

# ---------- Stage 2: runtime ----------
# Ganti php8.4 ke php8.3 / php8.5 kalau perlu. Laravel 13 = PHP 8.3-8.5.
FROM dunglas/frankenphp:1-php8.4 AS runtime

RUN apt-get update \
 && apt-get install -y --no-install-recommends curl ca-certificates \
 && rm -rf /var/lib/apt/lists/*

# Ekstensi PHP yang dibutuhkan Laravel + MySQL + Redis
RUN install-php-extensions \
      pdo_mysql \
      redis \
      opcache \
      intl \
      zip \
      gd \
      bcmath \
      pcntl

# Konfigurasi PHP production (opcache aktif, display_errors off)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"

WORKDIR /app

# Composer binary dipakai untuk dump autoload
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Vendor dulu (layer cache), baru source code
COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
 && rm -rf /root/.composer

# Jalan sebagai user biasa, bukan root
RUN groupadd -g 1000 app \
 && useradd -u 1000 -g app -m -s /bin/bash app \
 && mkdir -p storage/framework/cache storage/framework/sessions \
             storage/framework/views storage/logs bootstrap/cache \
 && chown -R app:app /app /data /config

COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/entrypoint

USER app

# Port internal (tidak dipublish ke internet, hanya diakses Caddy edge)
ENV SERVER_NAME=":8080"
EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
  CMD curl -fsS http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
