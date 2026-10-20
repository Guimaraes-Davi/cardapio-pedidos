# ---------- Estágio 1: compila o front (Vue + Tailwind) com o Vite ----------
FROM node:22 AS front

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build


# ---------- Estágio 2: imagem final com PHP ----------
FROM php:8.3-cli

# pdo_sqlite, mbstring, openssl, fileinfo, curl e as demais extensões exigidas
# pelo composer.lock já vêm compiladas na imagem oficial; falta só o unzip,
# que o Composer usa para extrair os pacotes
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Padrões da demonstração; qualquer variável definida no Render tem prioridade
ENV APP_NAME="Cardapio Pedidos" \
    APP_LOCALE=pt_BR \
    APP_FAKER_LOCALE=pt_BR \
    APP_TIMEZONE=America/Sao_Paulo \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    COMPOSER_ALLOW_SUPERUSER=1

# Dependências primeiro, para o Docker reaproveitar esta camada quando só o código mudar.
# Inclui as dependências de dev de propósito: os seeders usam o Faker.
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .
COPY --from=front /app/public/build ./public/build

RUN composer dump-autoload --optimize \
    && mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && sed -i 's/\r$//' docker/start.sh

EXPOSE 10000

CMD ["sh", "docker/start.sh"]
