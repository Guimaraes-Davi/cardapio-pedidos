#!/bin/sh
set -e

# Banco SQLite dentro do container: some a cada reinício, então é recriado aqui
touch database/database.sqlite

# Demonstração: sempre começa do zero com os dados de exemplo
php artisan migrate:fresh --seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

# exec: o servidor vira o processo principal e recebe o sinal de parada do Render
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
