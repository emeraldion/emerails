#
#	Project EmeRails - Codename Ocarina
#
#	Copyright (c) 2008, 2017 Claudio Procida
#	http://www.emeraldion.it
#
#
FROM php:5.6-apache

# Install the PHP extensions we need
RUN set -ex; \
	\
	apt-get update; \
	apt-get upgrade -y; \
	rm -rf /var/lib/apt/lists/*; \
	\
	docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy app resources
COPY ./ /var/www/html/
# Copy Docker entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/

# Declare Docker entrypoint script
ENTRYPOINT ["docker-entrypoint.sh"]
# Command on container startup
CMD ["apache2-foreground"]
