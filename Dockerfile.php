FROM php:8.2-fpm-alpine AS BUILD
COPY . /var/www/html/
#COPY composer_install.sh composer.json /var/www/html/
RUN apk add --no-cache git libzip-dev zip \
    && docker-php-ext-install zip \
    && cd /var/www/html \
    && chmod +x composer_install.sh && ./composer_install.sh \ 
    && mv composer.phar /usr/local/bin/composer \
    && composer install --no-cache \
    && rm composer_install.sh 

FROM php:8.2-fpm-alpine
WORKDIR /var/www/html
COPY --from=BUILD /var/www/html /var/www/html
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.2.16/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo pdo_pgsql


#RUN apk update && apk add --no-cahche \
#    libpq-dev \
#    && docker-php-ext-install pdo pdo_pgsql