FROM php:8.2-fpm

#Set the working dir
WORKDIR /srv

# Install system dependencies and required tools
# Install system dependencies and required tools
RUN apt-get update && \
    apt-get install -y \
        libpq-dev \
        git \
        unzip \
        zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install PHP extensions
RUN  docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
     docker-php-ext-install pdo_pgsql pgsql opcache
