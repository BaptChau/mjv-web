# ---- Stage 1: build JS/CSS assets ----
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
COPY webpack.config.js postcss.config.mjs ./
# Copy vendor UX packages needed by Webpack Encore (installed via composer)
COPY assets/ ./assets/
RUN npm ci
# vendor/symfony/ux-* assets are referenced by encore — copy them after npm ci
COPY vendor/ ./vendor/
RUN npm run build

# ---- Stage 2: PHP runtime ----
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

# Copy compiled assets from stage 1
COPY --from=assets /app/public/build /app/public/build

# Expose port
EXPOSE 8000

# Default command
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
