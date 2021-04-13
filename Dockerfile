FROM php:8.0-fpm-alpine

WORKDIR /var/www/html

# install packages and build tools
RUN apk --no-cache --update add \
    gcc musl-dev make curl bash openssl openssl-dev autoconf shadow \
    freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev curl libxml2-dev libmemcached-dev icu-dev g++ \
    libzip libzip-dev libressl3.1-libcrypto oniguruma oniguruma-dev libgcrypt-dev libxslt-dev \
    gettext-dev gmp-dev imap-dev unzip git

RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install soap
RUN docker-php-ext-install zip
RUN docker-php-ext-install imap
RUN docker-php-ext-install opcache
RUN docker-php-ext-install calendar
RUN docker-php-ext-install sockets
RUN docker-php-ext-install exif
RUN docker-php-ext-install gettext
RUN docker-php-ext-install gmp
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install intl
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install xsl
RUN docker-php-ext-install mbstring
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install gd
RUN docker-php-ext-enable imap

RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install composer: This could be removed and run in it's own container
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# xdebug.remote_connect_back = true does NOT work in docker
COPY ./conf.d/xdebug.ini $PHP_INI_DIR/conf.d/xdebug.ini

# Xdebug
# Note that "host.docker.internal" is not currently supported on Linux. This nasty hack tries to resolve it
# Source: https://github.com/docker/for-linux/issues/264
#RUN ip -4 route list match 0/0 | awk '{print $3" host.docker.internal"}' >> /etc/hosts
RUN ip -4 route list match 0/0 | awk '{print "xdebug.client_host="$3}' >> /usr/local/etc/php/php.ini
