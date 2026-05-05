FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libicu-dev \
    python3 python3-pip python3-venv \
    && docker-php-ext-install pdo pdo_pgsql zip intl
RUN git config --global --add safe.directory /app

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Setup Python venv and install scraper dependencies
COPY scripts/requirements.txt /tmp/requirements.txt
RUN python3 -m venv /opt/scraper-venv \
    && /opt/scraper-venv/bin/pip install --no-cache-dir -r /tmp/requirements.txt \
    && /opt/scraper-venv/bin/playwright install --with-deps chromium

# Copy application source
COPY . /app

# Expose port
EXPOSE 8000

# Default command
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
