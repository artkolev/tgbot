FROM php:8.4-fpm-alpine

RUN echo https://mirror.yandex.ru/mirrors/alpine/latest-stable/main > /etc/apk/repositories; \
    echo https://mirror.yandex.ru/mirrors/alpine/latest-stable/community >> /etc/apk/repositories

RUN apk add --no-cache --update \
    autoconf \
    g++ \
    make \
    git \
    curl \
    openssl-dev \
    libmemcached-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    unzip \
    icu-dev;


RUN rm -rf /var/lib/apt/lists/*;

# pdo
RUN docker-php-ext-install pdo \
    && docker-php-ext-install pdo_mysql \
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install intl \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl;

# php config
RUN touch /usr/local/etc/php/php.ini \
    && echo "date.timezone=Europe/Moscow" >> /usr/local/etc/php/php.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/php.ini \
    && echo "expose_php=Off" >> /usr/local/etc/php/php.ini


WORKDIR /app

# startup
CMD ["php-fpm"]
