FROM php:8.4-cli-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    libzip-dev \
    sqlite-dev \
    sqlite \
    freetype-dev \
    libjpeg-turbo-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Configure git safe directory
RUN git config --global --add safe.directory /var/www/html

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Ensure storage permissions
RUN mkdir -p storage bootstrap/cache database && chmod -R 777 storage bootstrap/cache database

EXPOSE 8000

CMD ["sh", "-c", "git config --global --add safe.directory /var/www/html && composer install --no-interaction --ignore-platform-reqs && touch database/database.sqlite && php artisan key:generate --force && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"]
