FROM php:7-apache
LABEL maintainer="edmurcardoso@gmail.com"

RUN apt-get update && apt-get install --assume-yes --fix-missing libssl-dev libxml2-dev libicu-dev libsqlite3-dev libsqlite3-0 git unzip supervisor wget cron dos2unix
RUN docker-php-ext-install intl bcmath pdo pdo_sqlite mbstring opcache soap ctype json xml tokenizer

WORKDIR /var/www/html/
RUN wget https://getcomposer.org/composer.phar
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN echo "* * * * * /usr/local/bin/php /var/www/html/artisan schedule:run > /proc/1/fd/1 2>&1"  | crontab
RUN service cron restart
RUN service cron reload

COPY . /var/www/html/
RUN chmod 777 -R /var/www/html/storage
RUN chmod 777 -R /var/www/html/bootstrap/cache

RUN php composer.phar install --no-interaction --no-dev --optimize-autoloader
RUN touch database/db.sqlite
RUN chmod 777 database/db.sqlite
RUN cp -f .env.example .env
RUN php artisan key:generate
RUN php artisan migrate --seed --force

COPY apache.conf /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite && service apache2 restart

EXPOSE 80
ENTRYPOINT php artisan env:ensure && cron -f -L 8 & docker-php-entrypoint && apache2-foreground
