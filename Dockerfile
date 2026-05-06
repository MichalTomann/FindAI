FROM php:8.2-apache
RUN docker-php-ext-install mysqli
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-enabled/dir-php-first.conf
