# Tahap 1: Gunakan image dasar PHP-Apache Anda
FROM php:8.2-apache

# Tahap 2: Salin executable Composer dari image resminya
# Ini adalah cara paling modern dan efisien untuk menambahkan Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Lanjutkan dengan instalasi ekstensi Anda yang sudah ada
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip \
    default-mysql-client \
    && docker-php-ext-install mysqli

# Hilangkan warning hostname di log Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf