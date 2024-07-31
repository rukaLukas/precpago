FROM php:8-fpm

# Copy composer.lock and composer.json into the working directory
# COPY composer.lock composer.json /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Install dependencies for the operating system software
RUN apt-get update && apt-get install -y \
    build-essential \
    zip \
    vim \
    git \
    libonig-dev \
    curl \
    wget

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install default extensions for laravel
RUN docker-php-ext-install mbstring pcntl

# Copy existing application directory contents to the working directory
COPY . /var/www/html

# Copy config php.ini
COPY ./php/php.ini /usr/local/etc/php/

# Assign permissions of the working directory to the www-data user
RUN chown -R www-data:www-data \
     /var/www/html/storage \
     /var/www/html/bootstrap/cache

# Install composer (php package manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-interaction

# Expose port 9000 and start php-fpm server (for FastCGI Process Manager)
EXPOSE 9000
# ENTRYPOINT cp .env.example .env && php-fpm
CMD [ "php-fpm" ]
