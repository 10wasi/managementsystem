FROM php:8.2-apache

RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork

RUN a2enmod rewrite

RUN printf '<Directory /var/www/html>\n\tAllowOverride All\n</Directory>\n' > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_sqlite

COPY . /var/www/html/

RUN chmod -R 777 /var/www/html/assets/uploads /var/www/html/database

EXPOSE 80
