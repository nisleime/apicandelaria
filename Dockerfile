# Use a imagem oficial do PHP com Apache
FROM php:8.0-apache

# Ativar mod_rewrite para o Apache
RUN a2enmod rewrite

# Instalar extensões necessárias para PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Instalar o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos da API para o contêiner
COPY ./ /var/www/html/

# Instalar Slim Framework e seus middlewares
RUN composer require slim/slim:"^3.0" slim/php-view slim/twig-view slim/psr7

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expor a porta 80 para acessar o servidor
EXPOSE 80

# Comando para iniciar o servidor Apache
CMD ["apache2-foreground"]
