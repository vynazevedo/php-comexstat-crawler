FROM php:8.3-cli-alpine

ARG SWOOLE_VERSION=5.1.2
ARG COMPOSER_VERSION=2.7.1

LABEL maintainer="Vinicius Azevedo <viniciusdiazevedo@gmail.com>" \
      description="Crawler de alta performance para dados do ComexStat usando PHP 8.3 e Swoole" \
      version="1.0.0"

ENV TZ=America/Sao_Paulo

RUN apk add --no-cache \
        $PHPIZE_DEPS \
        libstdc++ \
        openssl-dev \
        git \
        linux-headers \
    && pecl install swoole-${SWOOLE_VERSION} \
    && docker-php-ext-enable swoole \
    && apk del $PHPIZE_DEPS

COPY --from=composer:${COMPOSER_VERSION} /usr/bin/composer /usr/bin/composer

RUN echo "memory_limit=1G" > $PHP_INI_DIR/conf.d/memory-limit.ini \
    && echo "swoole.use_shortname=Off" > $PHP_INI_DIR/conf.d/swoole.ini \
    && echo "opcache.enable=1" > $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.enable_cli=1" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.jit=1255" >> $PHP_INI_DIR/conf.d/opcache.ini \
    && echo "opcache.jit_buffer_size=128M" >> $PHP_INI_DIR/conf.d/opcache.ini

RUN echo "expose_php=Off" > $PHP_INI_DIR/conf.d/security.ini \
    && echo "allow_url_fopen=Off" >> $PHP_INI_DIR/conf.d/security.ini

RUN echo "swoole.enable_preemptive_scheduler=On" >> $PHP_INI_DIR/conf.d/swoole.ini \
    && echo "swoole.enable_coroutine=On" >> $PHP_INI_DIR/conf.d/swoole.ini \
    && echo "swoole.enable_library=On" >> $PHP_INI_DIR/conf.d/swoole.ini

RUN addgroup -S appgroup && adduser -S appuser -G appgroup

WORKDIR /app

COPY --chown=appuser:appgroup . .

RUN composer install --no-interaction --no-progress --optimize-autoloader --prefer-dist \
    && composer dump-autoload --optimize --classmap-authoritative

RUN mkdir -p data && chown -R appuser:appgroup data && chmod 755 data

USER appuser

HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
    CMD php -r 'exit(extension_loaded("swoole") ? 0 : 1);'

CMD ["php", "src/bootstrap.php"]