FROM php:8.2-apache

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    unzip git curl libicu-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Activer mod_rewrite
RUN a2enmod rewrite

# Télécharger et installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier tout le code source dans le container
COPY . /var/www/html/

# Définir le dossier de travail
WORKDIR /var/www/html

# Installer les dépendances PHP Symfony (prod uniquement)
RUN composer install --no-dev --optimize-autoloader

# Changer le document root d'Apache vers /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Autoriser l'utilisation de .htaccess dans /public
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf
