FROM php:8.2-apache

RUN sed -i 's/^LoadModule mpm_event_module/# LoadModule mpm_event_module/' /etc/apache2/mods-enabled/*.conf || true
RUN sed -i 's/^LoadModule mpm_worker_module/# LoadModule mpm_worker_module/' /etc/apache2/mods-enabled/*.conf || true
RUN sed -i 's/^LoadModule mpm_prefork_module/LoadModule mpm_prefork_module/' /etc/apache2/mods-enabled/*.conf || true

RUN a2enmod rewrite

RUN printf '<Directory /var/www/html>\n\tAllowOverride All\n</Directory>\n' > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_sqlite

COPY . /var/www/html/

RUN chmod -R 777 /var/www/html/assets/uploads /var/www/html/database

EXPOSE 80
