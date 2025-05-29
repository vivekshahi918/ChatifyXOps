FROM php:8.2-apache

# Copy everything from the app directory into the container's web root
COPY ../ /var/www/html/

# Install MySQLi extension for PHP
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

EXPOSE 80


# password for argo "aQiTzq7k43taq1P6" user"admin"
