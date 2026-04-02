FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libpq-dev postgresql-client tcpdump \
    && docker-php-ext-install pdo_pgsql \
    && pecl install pcov xdebug \
    && docker-php-ext-enable pcov xdebug \
    && { \
        echo 'xdebug.mode=develop'; \
        echo 'xdebug.start_with_request=no'; \
    } > /usr/local/etc/php/conf.d/zz-xdebug-settings.ini \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY docker/start-container.sh /usr/local/bin/start-container
COPY docker/fastcgi-capture-start.sh /usr/local/bin/fastcgi-capture-start
COPY docker/fastcgi-capture-stop.sh /usr/local/bin/fastcgi-capture-stop

RUN chmod +x /usr/local/bin/start-container /usr/local/bin/fastcgi-capture-start /usr/local/bin/fastcgi-capture-stop

CMD ["start-container"]
