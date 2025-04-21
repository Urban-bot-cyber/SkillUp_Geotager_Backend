FROM php:8.2-apache

# Set environment variables to non-interactive
ENV DEBIAN_FRONTEND=noninteractive

# Install dependencies
# Install system dependencies and PHP extensions
# Install system dependencies and PHP extensions
# RUN apt-get update && apt-get install -y \
#     git \
#     zip \
#     unzip \
#     libpq-dev \
#     libonig-dev \
#     libzip-dev \
#     libexif-dev \
#     build-essential \
#     --no-install-recommends \
#  && docker-php-ext-install \
#     pdo \
#     pdo_pgsql \
#     mbstring \
#     zip \
#     opcache \
#     exif \
#     pcntl \
#  && docker-php-ext-enable opcache \
#  && apt-get clean \
#  && rm -rf /var/lib/apt/lists/* \
#  && a2enmod rewrite

# ---- NEW SEPARATE RUN COMMANDS ----
    RUN apt-get update

    RUN apt-get install -y \
        git \
        zip \
        unzip \
        libpq-dev \
        libonig-dev \
        libzip-dev \
        libexif-dev \
        build-essential \
        --no-install-recommends
    
    # Separate command specifically for installing PHP extensions
    RUN docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        zip \
        opcache \
        exif \
        pcntl
    
    RUN docker-php-ext-enable opcache
    
    RUN apt-get clean && rm -rf /var/lib/apt/lists/*
    
    RUN a2enmod rewrite
    # ---- END OF NEW COMMANDS ----
    

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy only Composer files and install dependencies without scripts
COPY composer.* ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts

# Copy application code
COPY . .

RUN composer dump-autoload --optimize --no-dev && \
    php artisan package:discover


# Copy custom Apache config (Make sure this config listens on port 80)
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite && a2ensite 000-default

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port 80 (Standard HTTP port Apache listens on by default)
EXPOSE 80

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Use the entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command if entrypoint succeeds
CMD ["apache2-foreground"]