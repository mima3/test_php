FROM debian:bookworm-slim

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    apache2-utils \
    build-essential \
    autoconf \
    bison \
    file \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /usr/local/src

# ホスト側の php-2.0.1 ディレクトリをそのままコピー
COPY php-2.0.1 /usr/local/src/php-2.0.1

WORKDIR /usr/local/src/php-2.0.1

# install が対話式なら空改行を流して進める
RUN printf '\n\n\n\n\n\n\n\n\n\n' | ./install || true

WORKDIR /usr/local/src/php-2.0.1/src

# 古いコード向けに少し緩めてビルド
RUN make CFLAGS="-O2 -fcommon -Wno-error" || \
    (make clean || true; make CFLAGS="-O0 -fcommon")

RUN mkdir -p /usr/lib/cgi-bin \
 && cp php.cgi /usr/lib/cgi-bin/php.cgi \
 && chmod 755 /usr/lib/cgi-bin/php.cgi

# PHP/FI 2.0 が期待する古い DOCUMENT_ROOT に合わせる
RUN mkdir -p /usr/local/etc \
 && ln -s /var/www/html /usr/local/etc/htdocs

# PHP/FI 2.0 が期待する古いログ用ディレクトリを作る
# ログを有効にすると２回目以降のアクセスでエラーになるので一旦解除
#RUN mkdir -p /usr/local/etc/httpd/cgi-data \
# && chmod 777 /usr/local/etc/httpd/cgi-data

RUN a2enmod cgi actions alias
COPY apache/php2.conf /etc/apache2/conf-available/php2.conf
RUN a2enconf php2

WORKDIR /var/www/html

EXPOSE 80

CMD ["apachectl", "-D", "FOREGROUND"]