FROM debian:bookworm

ENV DEBIAN_FRONTEND=noninteractive
ENV PHP_DEBUG_PREFIX=/opt/php-debug
ENV PATH=/opt/php-debug/bin:$PATH

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        autoconf \
        bison \
        build-essential \
        ca-certificates \
        curl \
        gdb \
        git \
        libffi-dev \
        libonig-dev \
        libsqlite3-dev \
        libssl-dev \
        libxml2-dev \
        pkg-config \
        re2c \
        unzip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /usr/src/php-src

COPY php-8.5.4/ /usr/src/php-src/

RUN ./buildconf --force \
    && ./configure \
        --prefix="${PHP_DEBUG_PREFIX}" \
        --enable-debug \
        --enable-phpdbg \
        --enable-cli \
        --enable-mbstring \
        --disable-cgi \
        --with-ffi \
        --with-libxml \
        --with-openssl \
        --with-sqlite3 \
        --with-pdo-sqlite \
    && make -j"$(nproc)" \
    && make install

WORKDIR /usr/src

RUN curl -fsSL https://getcomposer.org/installer -o composer-setup.php \
    && "${PHP_DEBUG_PREFIX}/bin/php" composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

RUN curl -fsSL https://xdebug.org/files/xdebug-3.5.1.tgz -o xdebug.tgz \
    && tar -xzf xdebug.tgz \
    && rm xdebug.tgz

WORKDIR /usr/src/xdebug-3.5.1

RUN phpize \
    && ./configure --with-php-config="${PHP_DEBUG_PREFIX}/bin/php-config" \
    && make -j"$(nproc)" \
    && make install \
    && XDEBUG_SO="$(find "${PHP_DEBUG_PREFIX}/lib/php/extensions" -name xdebug.so -print -quit)" \
    && test -n "${XDEBUG_SO}" \
    && { \
        echo "zend_extension=${XDEBUG_SO}"; \
        echo "xdebug.mode=develop"; \
        echo "xdebug.start_with_request=no"; \
    } > "${PHP_DEBUG_PREFIX}/lib/php.ini"

WORKDIR /work

CMD ["sleep", "infinity"]
