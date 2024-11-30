# Baseada no Debian 12
FROM debian:12

# Variáveis para não pedir interações durante instalações
ENV DEBIAN_FRONTEND=noninteractive

# Atualiza o sistema e instala pacotes necessários
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    php-mysql \
    curl \
    git \
    unzip \
    wget \
    vim \
    && apt-get clean

# Ativa o módulo de regravação do Apache (necessário para muitos frameworks PHP)
RUN a2enmod rewrite

# Configura o diretório raiz do Apache
WORKDIR /var/www/html

# Copia os arquivos do host para o contêiner
COPY . /var/www/html/

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html

# Exibe logs no terminal
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Porta exposta pelo Apache
EXPOSE 80

# Comando para iniciar o Apache
CMD ["apachectl", "-D", "FOREGROUND"]
