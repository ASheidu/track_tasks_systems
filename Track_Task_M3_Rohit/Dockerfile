FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev ca-certificates \
    && docker-php-ext-install pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/*

# Only public/ can be served by Apache.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -ri 's/\*:80/*:10000/' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY includes/ ./includes/
COPY public/ ./public/

RUN find public -name 'local-task-test.php' -delete \
    && printf 'display_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/production.ini

EXPOSE 10000
CMD ["apache2-foreground"]
