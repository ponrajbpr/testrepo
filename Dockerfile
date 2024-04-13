FROM php:8.1-apache
LABEL Name="Ponraj"
RUN apt-get update
RUN apt-get update && apt-get install -y apache2
EXPOSE 80
RUN apt-get install zip unzip
RUN apt-get install nano
RUN apt-get update
RUN a2enmod rewrite
RUN chmod -R 777 /var/www/html
