#!/bin/bash
set -e

# El volumen storage_data solo persiste storage/; el resto de public/
# (incluido build/) viene siempre fresco de la imagen recién construida.
# Se sincroniza hacia el volumen compartido con nginx en cada arranque
# para evitar que un build viejo quede "atrapado" en el volumen.
mkdir -p /var/www/public
find /var/www/public -mindepth 1 -maxdepth 1 ! -name storage -exec rm -rf {} +
cp -a /var/www/public-src/. /var/www/public/

# Normaliza permisos en cada arranque, evitando fallos silenciosos de
# escritura en logs/cache si el volumen quedó con dueño mixto.
chown -R www-data:www-data /var/www/public /var/www/storage

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan storage:link --force || true

exec "$@"