FROM php:7.3-apache
MAINTAINER sinkcup <sinkcup@gmail.com>

ARG APP_ENV=production
ARG SPEED=keep
WORKDIR /var/www/laravel
COPY speed /var/www/laravel/
RUN ./speed -s $SPEED apt

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sL https://deb.nodesource.com/setup_12.x | bash -

RUN apt-get update \
    && apt-get install -y \
    cron \
    icu-devtools \
    jq \
    libfreetype6-dev libicu-dev libjpeg62-turbo-dev libpng-dev libsasl2-dev libssl-dev libwebp-dev libxpm-dev libzip-dev \
    nodejs \
    unzip \
    zlib1g-dev
RUN if [ "$APP_ENV" != "production" ]; then apt-get install -y git librsvg2-bin vim; fi
RUN apt-get clean \
    && apt-get autoclean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && yes '' | pecl install redis \
    && docker-php-ext-configure gd --with-freetype-dir --with-gd --with-jpeg-dir --with-png-dir --with-webp-dir --with-xpm-dir --with-zlib-dir \
    && docker-php-ext-install gd intl pdo_mysql zip \
    && docker-php-ext-enable opcache redis
RUN if [ "$APP_ENV" != "production" ]; then \
        cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug \
    ; fi

COPY composer.json /var/www/laravel/
COPY composer.lock /var/www/laravel/
RUN ./speed -s $SPEED composer \
    && composer install --no-autoloader --no-scripts --no-dev

# Compiling Assets. uncomment these lines after you install Laravel UI
#COPY package.json /var/www/laravel/
#COPY package-lock.json /var/www/laravel/
#RUN npm install
#COPY resources/js /var/www/laravel/resources/js
#COPY resources/sass /var/www/laravel/resources/sass
#COPY webpack.mix.js /var/www/laravel/
#RUN npm run production

COPY app /var/www/laravel/app
COPY artisan /var/www/laravel/artisan
COPY bootstrap /var/www/laravel/bootstrap
COPY config /var/www/laravel/config
COPY database /var/www/laravel/database
COPY public /var/www/laravel/public
COPY resources /var/www/laravel/resources
COPY routes /var/www/laravel/routes
COPY server.php /var/www/laravel/server.php
COPY storage /var/www/laravel/storage
RUN if [ "$APP_ENV" = "production" ]; then \
        composer install --optimize-autoloader --no-dev \
    ; else \
        composer install \
    ; fi

RUN rm -f public/storage \
    && php artisan storage:link \
    && touch database/database.sqlite

COPY docker/ /
RUN a2enmod rewrite headers \
    && a2ensite laravel \
    && a2dissite 000-default

COPY . /var/www/laravel/
RUN chown www-data:www-data bootstrap/cache \
    && chown -R www-data:www-data storage/

CMD ["docker-laravel-entrypoint"]
