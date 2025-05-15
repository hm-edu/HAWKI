FROM php:8.4.7RC1-fpm-alpine AS build
COPY . /var/www/html/
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.7/install-php-extensions /usr/local/bin/
RUN apk add --no-cache git libzip-dev zip \
    && docker-php-ext-install zip \
    && cd /var/www/html \
    && chmod +x composer_install.sh && ./composer_install.sh \ 
    && mv composer.phar /usr/local/bin/composer \
    && composer install --no-cache \
    && install-php-extensions pdo pdo_pgsql apcu \
    && rm /usr/local/etc/php/conf.d/docker-php-ext-zip.ini \
    && mv custom-php.ini /usr/local/etc/php/conf.d/

FROM php:8.4.7RC1-fpm-alpine 
WORKDIR /var/www/html
COPY --from=build /var/www/html /var/www/html
# pdo pdo_pgsql dependencies
COPY --from=build /usr/lib/libpgtypes.so.* /usr/lib/
COPY --from=build /usr/lib/libpq.so.* /usr/lib/
COPY --from=build /usr/lib/libecpg.so.* /usr/lib
COPY --from=build /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d/  
COPY --from=build /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=build /usr/local/include/php/ext /usr/local/include/php/ext
