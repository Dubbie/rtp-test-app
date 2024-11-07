# Use a lightweight PHP image
FROM php:8.2-cli

# Install dependencies for zip extension, including libzip
RUN apt-get update && \
    apt-get install -y \
    zlib1g-dev \
    libzip-dev && \
    docker-php-ext-configure zip && \
    docker-php-ext-install zip

# Set working directory
WORKDIR /var/www

# Copy app code
COPY src/ /var/www

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the composer.json file to the working directory
COPY src/composer.json /var/www

# Install dependencies
RUN composer install

# Copy the rest of the application files
COPY src/ /var/www

# Expose port if running as web server
EXPOSE 8000

# Run PHP server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www"]
