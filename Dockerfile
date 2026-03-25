FROM php:8.2-apache

RUN a2enmod rewrite

RUN sed -ri 's!/var/www/>!/var/www/>\\n\\tAllowOverride All!g' /etc/apache2/apache2.conf

RUN docker-php-ext-install pdo_sqlite

COPY . /var/www/html/

RUN chmod -R 777 /var/www/html/assets/uploads /var/www/html/database

EXPOSE 80
