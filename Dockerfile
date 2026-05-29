FROM composer/satis:latest AS satis-src

FROM php:8.4-fpm-alpine

RUN apk add --no-cache git zip unzip curl bash openssh-client libzip-dev && \
    docker-php-ext-install zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Pull satis from the official image — no composer install needed
COPY --from=satis-src /satis /satis
RUN ln -s /satis/bin/satis /usr/local/bin/satis

ENV COMPOSER_HOME=/var/www/.composer

WORKDIR /app

COPY composer.json /app/composer.json
RUN composer install --no-dev --no-interaction --prefer-dist

RUN mkdir -p /output /satis /var/www/.composer && \
    chown -R www-data:www-data /output /satis /var/www/.composer

COPY satis.json /satis/satis.json
COPY src/ /app/src/
COPY webhook/ /var/www/html/
COPY scripts/build.sh /usr/local/bin/satis-build
COPY scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/satis-build /usr/local/bin/entrypoint.sh && \
    chown www-data:www-data /satis/satis.json

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
