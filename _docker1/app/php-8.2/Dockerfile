FROM php:8.3-fpm

ARG USERGROUP
ARG DOCKER_PATH
ENV SUPERVISOR_PHP_USER="appuser"

RUN groupadd --force -g $USERGROUP appuser
RUN useradd -ms /bin/bash --no-user-group -g $USERGROUP -u $USERGROUP appuser

RUN apt-get update && apt-get install -y \
    apt-utils \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    zip \
    unzip \
    jpegoptim \
    optipng \
    ghostscript \
    gifsicle \
    ffmpeg && \
    #protobuf-compiler \
    #protobuf-compiler-grpc && \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm && \
    docker-php-ext-install pdo_mysql mysqli bcmath gd zip

# RUN apt-get update && apt-get install -y \
#   libmemcached-dev \
#   zlib1g-dev
# RUN pecl install memcached && docker-php-ext-enable memcached

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    && pecl install igbinary \
    && pecl install -D 'enable-redis-igbinary="yes"' redis \
    && docker-php-ext-enable igbinary redis

#RUN pecl install protobuf
#RUN pecl install grpc
#RUN docker-php-ext-enable grpc
#RUN docker-php-ext-enable protobuf

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

COPY nginx/certs/ /usr/local/share/ca-certificates/
COPY nginx/certs/ /etc/nginx/certs
RUN apt-get update && apt-get install -y \
    ca-certificates \
    wget \
    && update-ca-certificates

# RUN apt-get update && apt-get install -y \
#     postfix

RUN apt-get update && apt-get install -y nginx

RUN apt-get update && apt-get install -y \
    cron \
    supervisor

COPY app/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY app/cron/appuser.txt /var/spool/cron/crontabs/appuser
COPY app/cron/root.txt /var/spool/cron/crontabs/root
RUN echo "" >> /var/spool/cron/crontabs/appuser
RUN echo "" >> /var/spool/cron/crontabs/root
RUN chown appuser:appuser /var/spool/cron/crontabs/appuser
RUN chmod 0600 /var/spool/cron/crontabs/appuser /var/spool/cron/crontabs/root
RUN touch /var/log/cron.log
RUN service cron restart
COPY ${DOCKER_PATH}/bin/app_entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

#imagick RUN pecl install imagick && docker-php-ext-enable imagick

#xdebug RUN pecl install xdebug \
#xdebug     && docker-php-ext-enable xdebug

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /var/www

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]