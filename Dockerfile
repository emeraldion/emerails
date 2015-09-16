FROM php:5.6-apache
COPY ocarina/ /var/www/html/
COPY apache2.conf /etc/apache2/
