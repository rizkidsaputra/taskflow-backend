FROM php:8.2-fpm

# Install ekstensi PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copy kode aplikasi ke container
COPY . /var/www/html/

# Set permission untuk PHP-FPM
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/

# Expose port 9000 untuk PHP-FPM
EXPOSE 9000
