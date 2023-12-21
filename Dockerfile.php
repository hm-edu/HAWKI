FROM php:8.2-fpm-alpine
COPY . /var/www/html/

RUN apk add --no-cache git libzip-dev zip \
    && docker-php-ext-install zip \
    && cd /var/www/html \
    && chmod +x composer_install.sh && ./composer_install.sh \ 
    && mv composer.phar /usr/local/bin/composer \
    && composer install --no-cache \
    && rm composer_install.sh Dockerfile.caddy Dockerfile.php \
    && wget https://github.com/highlightjs/cdn-release/archive/refs/tags/11.9.0.zip \
    && unzip 11.9.0.zip && mv cdn-release-11.9.0/build /var/www/html/highlightjs && rm 11.9.0.zip
