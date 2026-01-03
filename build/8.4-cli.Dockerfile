FROM php:8.4-cli

RUN apt-get update && apt-get install -y cron openssl git unzip libzip-dev

RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Ajout de xsl ici
RUN install-php-extensions gettext iconv intl tidy zip sockets xsl pgsql mysqli pdo_mysql pdo_pgsql xdebug @composer

WORKDIR /var/php
EXPOSE 80