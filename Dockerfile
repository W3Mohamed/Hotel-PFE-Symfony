FROM php:8.2-apache

# Installer les extensions nécessaires à Symfony
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Active mod_rewrite pour Symfony
RUN a2enmod rewrite

# Copier les fichiers Symfony
COPY . /var/www/html/

# Positionne le bon répertoire de travail
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Ajouter config Apache pour rediriger vers /public
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
</Directory>' > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony
