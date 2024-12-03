# Usando uma imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instalar dependências necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql zip

# Ativar o mod_rewrite do Apache para redirecionamento de URLs
RUN a2enmod rewrite

# Instalar Composer (gerenciador de dependências PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Definir o diretório de trabalho dentro do container
WORKDIR /var/www/html

# Copiar o arquivo composer.json e instalar as dependências
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist

# Copiar todo o código da aplicação para o container
COPY . /var/www/html/

# Expõe a porta 80 para o Apache
EXPOSE 80

# Iniciar o Apache
CMD ["apache2-foreground"]
