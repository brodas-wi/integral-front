# --- ETAPA 1: Compilación de Frontend con Node.js/Vite ---
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copiar manifiestos de Node
COPY package*.json ./
RUN npm ci

# Copiar el resto del código necesario para Vite
COPY . .

# Generar el bundle de producción en /app/public/build
RUN npm run build


# --- ETAPA 2: Runtime PHP-FPM para Producción ---
FROM php:8.3-fpm-alpine AS production

# Instalar dependencias del sistema y extensiones PHP en Alpine (más liviano y seguro)
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    libzip-dev

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar el código fuente
COPY . /var/www

# Copiar los assets compilados desde la ETAPA 1
COPY --from=frontend-builder /app/public/build /var/www/public/build

# Instalar dependencias PHP de producción (sin dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Ajustar permisos de directorios críticos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]