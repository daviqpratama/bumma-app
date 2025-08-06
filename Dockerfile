FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
  git zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev curl gnupg \
  && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
  && apt-get install -y nodejs

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN npm ci --silent
RUN npm run build

RUN php artisan config:clear \
 && php artisan view:clear \
 && php artisan route:clear \
 && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
