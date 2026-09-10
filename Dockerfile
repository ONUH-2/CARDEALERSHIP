FROM php:8.3-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && printf '<Directory /var/www/html>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/cardealership.conf \
    && a2enconf cardealership

EXPOSE 10000

CMD ["sh", "-c", "PORT=${PORT:-10000}; sed -i \"s/Listen 80/Listen ${PORT}/; s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/\" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf; apache2-foreground"]
