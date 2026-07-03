FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo \
        pdo_mysql \
        zip \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ARG USER_UID=1000
ARG USER_GID=1000
RUN groupadd -g ${USER_GID} appuser \
    && useradd -u ${USER_UID} -g appuser -m appuser

WORKDIR /var/www/html

USER appuser