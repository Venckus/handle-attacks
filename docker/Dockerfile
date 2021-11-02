FROM php:7.4-apache

# ARG APP_VERSION
# ENV APP_VERSION=$APP_VERSION
ENV TZ=Europe/Vilnius
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN set -eux; \
    apt-get update && apt-get install -y --no-install-recommends \
    libmcrypt-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    # libcurl4-openssl-dev \
    libc-client-dev \
    # libssl-dev \
    libkrb5-dev \
    # php-common \
    curl \
    cron \
    wget \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    iproute2 \
    nano \
    git \
    libicu-dev \
    ; \
    docker-php-ext-configure intl; \
    docker-php-ext-install intl; \
    docker-php-ext-install pdo_mysql; \
    docker-php-ext-install zip; \
    docker-php-ext-install opcache; \
    # docker-php-composer-install --version=1.6; \
    PHP_OPENSSL=yes docker-php-ext-configure imap --with-kerberos --with-imap-ssl; \
    docker-php-ext-install imap; \
    apt-get autoclean; \
    docker-php-source delete; \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer; \
    rm -rf /var/www/html;

RUN composer self-update --1

COPY docker/apache.conf /etc/apache2/apache2.conf



# local
COPY docker/php.ini $PHP_INI_DIR/php.ini

RUN set -eux; \
    pecl install xdebug; \
    # pecl install uopz-6.1.2; \
    # docker-php-ext-enable uopz; \
    docker-php-ext-enable xdebug; \
    docker-php-source delete

ADD . /var/www
WORKDIR /var/www
RUN chown www-data:www-data -R /var/www
RUN chmod 777 -R /var/www

RUN cd /var/www; \
    composer install -n -o

RUN rm -f /var/run/apache2/apache2.pid

EXPOSE 80
# CMD ["apache2-foreground"]
# CMD [ "apachectl -D FOREGROUND" ]
# CMD [ "apache2 -DFOREGROUND" ]
