FROM php:8.3-apache
COPY scoreboard.html /var/www/html/scoreboard.html
COPY index.html /var/www/html/index.html
COPY api.php /var/www/html/api.php
RUN mkdir -p /data && chown www-data:www-data /data
EXPOSE 80