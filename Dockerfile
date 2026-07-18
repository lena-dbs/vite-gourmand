FROM php:8.2-apache

# Extensions PHP (pdo_mysql + mongodb) et dépendances de build
RUN apt-get update && apt-get install -y --no-install-recommends \
        libssl-dev pkg-config unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

# Composer (binaire copié depuis l'image officielle)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite expires deflate

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . /var/www/html/

# Installe les dépendances au build (vendor/ n'est plus versionné)
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD ["apache2ctl", "-D", "FOREGROUND"]
