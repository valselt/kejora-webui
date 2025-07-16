FROM php:8.2-apache


RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip \
    default-mysql-client \
    && docker-php-ext-install mysqli


RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
