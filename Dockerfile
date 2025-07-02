FROM php:8.3.6-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip
RUN git config --global --add safe.directory /app
# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files (optionnel si tu utilises un volume)
# COPY . .

# Expose port (optionnel, pour info)
EXPOSE 8000

# Default command (sera surchargé par docker-compose)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

