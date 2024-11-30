# Use a imagem oficial do PHP com Apache
FROM php:8.0-apache

# Ativar mod_rewrite para o Apache
RUN a2enmod rewrite

# Instalar dependências adicionais (se necessário, como PDO para MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Copiar os arquivos da API para o diretório do contêiner
COPY ./ /var/www/html/

# Expor a porta 80 para acessar o servidor
EXPOSE 80
