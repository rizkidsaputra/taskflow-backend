FROM php:8.2-apache

# Enable apache modules commonly needed
RUN a2enmod headers rewrite

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
