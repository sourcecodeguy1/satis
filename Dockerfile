FROM php:8.2-fpm-alpine

RUN apk add --no-cache git zip unzip curl bash openssh-client

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install satis globally with a fixed home so it's accessible at runtime
ENV COMPOSER_HOME=/usr/local/composer
RUN composer global require composer/satis --no-interaction && \
    ln -s /usr/local/composer/vendor/bin/satis /usr/local/bin/satis

WORKDIR /satis

RUN mkdir -p /output && chown -R www-data:www-data /output /satis

COPY satis.json /satis/satis.json
COPY webhook/ /var/www/html/
COPY scripts/build.sh /usr/local/bin/satis-build
COPY scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/satis-build /usr/local/bin/entrypoint.sh && \
    chown www-data:www-data /satis/satis.json

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
