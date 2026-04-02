FROM debian:bookworm-slim

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    apache2-utils \
    build-essential \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /usr/local/src

COPY php-108 /usr/local/src/php-108

WORKDIR /usr/local/src/php-108

# いちおう ROOTDIR も書き換えを試みる
RUN cp config.h config.h.orig && \
    awk 'BEGIN { done=0 } /ROOTDIR/ && done==0 { print "#define ROOTDIR \"/var/www/html\""; done=1; next } { print }' config.h.orig > config.h

# PHP 1 が古いパス /var/www/public_html を見に行っても通るようにする
RUN mkdir -p /var/www \
 && ln -s /var/www/html /var/www/public_html || true

# ログディレクトリを両方に用意
RUN mkdir -p /var/www/html/logs \
 && chmod 0777 /var/www/html/logs

RUN make CFLAGS="-O2 -fcommon -Wno-error" || \
    make CFLAGS="-O0 -fcommon"

RUN mkdir -p /usr/lib/cgi-bin \
 && cp phpl.cgi phpf.cgi phplmon.cgi phplview.cgi /usr/lib/cgi-bin/ \
 && chmod 755 /usr/lib/cgi-bin/phpl.cgi \
               /usr/lib/cgi-bin/phpf.cgi \
               /usr/lib/cgi-bin/phplmon.cgi \
               /usr/lib/cgi-bin/phplview.cgi

RUN a2enmod cgi alias

COPY apache/php1.conf /etc/apache2/conf-available/php1.conf
RUN a2enconf php1

WORKDIR /var/www/html

EXPOSE 80

CMD ["apachectl", "-D", "FOREGROUND"]