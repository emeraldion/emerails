FROM php:5.6-apache

RUN apt-get update
RUN apt-get -y upgrade

# Enable Apache mod_rewrite
RUN a2enmod rewrite

COPY ./ /var/www/html/
