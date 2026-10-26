FROM php:8.3-cli-alpine

# Install system dependencies & PHP extensions for SQLite, MySQL, and File/Zip handling
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    sqlite \
    sqlite-dev \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring zip exif pcntl bcmath gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY package.json package-lock.json* ./
RUN npm install

COPY . .

RUN npm run build \
    && rm -rf node_modules \
    && composer dump-autoload --optimize \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database \
    && chmod -R 777 /app/storage /app/bootstrap/cache /app/database \
    && chmod +x /app/docker-entrypoint.sh

EXPOSE 8080 7860

ENTRYPOINT ["/app/docker-entrypoint.sh"]
