FROM php:8.1-apache
WORKDIR /var/www/php
COPY . .
RUN docker-php-ext-install pdo pdo_mysql
EXPOSE 80
CMD ["apache2-foreground"]
