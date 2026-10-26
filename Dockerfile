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

# Copia intacta del public/ recién compilado (código público + build/ nuevo),
# usada por el entrypoint para resincronizar el volumen compartido con nginx
# en cada arranque del contenedor, evitando que quede un build viejo "pegado".
RUN cp -a /var/www/public /var/www/public-src

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Ajustar permisos de directorios críticos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public-src /var/www/public

# Nota: ya NO se fija USER www-data aquí. El entrypoint necesita correr
# como root para poder limpiar/sincronizar el volumen app_public sin
# toparse con archivos de dueño distinto dejados por builds anteriores.
# php-fpm baja privilegios internamente a través de su pool (www.conf,
# que por defecto ya corre los workers como www-data).

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]