FROM php:8.2-cli

RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_sqlite
RUN docker-php-ext-install pdo_pgsql

WORKDIR /var/www/html
COPY . /var/www/html/

RUN mkdir -p /var/www/html/assets/uploads /var/www/html/database \
    && chmod -R 777 /var/www/html/assets/uploads /var/www/html/database \
    && php /var/www/html/database/install.php

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
