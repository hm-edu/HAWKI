FROM php:8.2-fpm-alpine AS BUILD
COPY . /var/www/html/
COPY composer_install.sh composer.json /var/www/html/
RUN apk add --no-cache git libzip-dev zip \
    && docker-php-ext-install zip \
    && cd /var/www/html \
    && chmod +x composer_install.sh && ./composer_install.sh \ 
    && mv composer.phar /usr/local/bin/composer \
    && composer install --no-cache \
    && rm composer_install.sh \
    && wget https://github.com/highlightjs/cdn-release/archive/refs/tags/11.9.0.zip \
    && unzip 11.9.0.zip && mv cdn-release-11.9.0/build /var/www/html/src/highlightjs && rm 11.9.0.zip

RUN apk add --no-cache npm nodejs \
    #&& npm install --save-dev webpack webpack-cli webpack-merge html-webpack-plugin \
    #&& npm install js-tiktoken \
    && npm install \
    && npx webpack --config config.production.js \
    && mv -f dist/interface.js src/interface.js \
    && mv vendor src

FROM php:8.2-fpm-alpine
WORKDIR /var/www/html
COPY --from=BUILD /var/www/html/src /var/www/html