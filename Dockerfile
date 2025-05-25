# Debian + Apache + PHP - Docker Hub
FROM php:8.2-apache

# Arbeitsverzeichnis setzen
WORKDIR /var/www/app

# Projekt kopieren
COPY ./projekt /var/www/app

# Public-Verzeichnis verlinken als Apache root
RUN rm -rf /var/www/html && \
    ln -s /var/www/app/public /var/www/html

RUN echo "Servername localhost" >> /etc/apache2/apache2.conf

# Installiere systemweite Tools, die Composer benoetigt:
# - zip/unzip: zum Entpacken von Paketarchiven
# - git: als Fallback, falls zip nicht funktioniert
# - libzip-dev: Benoetigt, um PHPs zip-Extension zu bauen
#
# Ausserdem:
# - Installiere und aktiviere die PHP-Erweiterung "zip",
# damit Composer .zip-Archive verarbeiten kann.
# - pdo: generelle PDO-Unterstuetzung fuer Datenbankzugriffe (MariaDB)
# - pdo_mysql: Treiber fuer MySQL-kompatible Datenbanken wie: MariaDB oder MySQL

RUN apt-get update && apt-get install -y \
    zip \
	unzip \
	git \
	libzip-dev \
	&& docker-php-ext-install zip pdo pdo_mysql

# Composer installieren
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }" && \
	php composer-setup.php && \
	php -r "unlink('composer-setup.php');" && \
	mv composer.phar /usr/local/bin/composer

# Composer-Abhaengigkeiten installieren
RUN composer install