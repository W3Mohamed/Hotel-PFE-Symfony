FROM php:8.2-apache

# Installer dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev libzip-dev zip unzip git curl libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Activer mod_rewrite (important pour .htaccess de Symfony)
RUN a2enmod rewrite

# Copier tous les fichiers dans le container
COPY . /var/www/html/

# Définir le dossier de travail
WORKDIR /var/www/html

# Donner les bons droits à Apache
RUN chown -R www-data:www-data /var/www/html

# Indiquer à Apache d’utiliser le dossier public comme racine
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Ajout config .htaccess pour Symfony
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf
