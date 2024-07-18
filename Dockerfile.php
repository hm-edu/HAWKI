FROM php:8.3-fpm-alpine AS BUILD
COPY . /var/www/html/
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.2.16/install-php-extensions /usr/local/bin/
RUN apk add --no-cache git libzip-dev zip \
    && docker-php-ext-install zip \
    && cd /var/www/html \
    && chmod +x composer_install.sh && ./composer_install.sh \ 
    && mv composer.phar /usr/local/bin/composer \
    && composer install --no-cache \
    && install-php-extensions pdo pdo_pgsql 

FROM php:8.3-fpm-alpine
WORKDIR /var/www/html
COPY --from=BUILD /var/www/html /var/www/html
# pdo pdo_pgsql dependencies
COPY --from=BUILD /usr/lib/libpgtypes.so.* /usr/lib/
COPY --from=BUILD /usr/lib/libpq.so.* /usr/lib/
COPY --from=BUILD /usr/lib/libecpg.so.* /usr/lib
COPY --from=BUILD /usr/local/etc/php/conf.d/docker-php-ext-pdo_pgsql.ini /usr/local/etc/php/conf.d/
COPY --from=BUILD /usr/local/lib/php/extensions /usr/local/lib/php/extensions
