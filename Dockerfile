# syntax=docker/dockerfile:1.7

# ============================================================================
# 阶段 1: Composer — 安装生产依赖
# ============================================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
        --no-dev \
        --no-scripts \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --no-interaction

# ============================================================================
# 阶段 2: Node — 构建前端资产
# ============================================================================
FROM node:22-slim AS frontend

WORKDIR /app

RUN corepack enable

COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
RUN --mount=type=cache,target=/root/.local/share/pnpm/store \
    pnpm install --ignore-scripts --frozen-lockfile

# vendor 不在 frontend 阶段时,CSS 里 @import "../../vendor/.../*.css" 解析不到
# 从 vendor 阶段拷过来,仅供 pnpm build 解析路径用 (不会进入最终镜像)
COPY --from=vendor /app/vendor /app/vendor

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN pnpm build

# ============================================================================
# 阶段 3: 运行时 — FrankenPHP + Laravel Octane
# ============================================================================
FROM dunglas/frankenphp:1-php8.4-bookworm AS runtime

# 装系统依赖(需要的 PHP 扩展)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libssl-dev \
        libxml2-dev \
        libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && install-php-extensions \
        pdo_sqlite \
        pdo_mysql \
        mbstring \
        intl \
        zip \
        bcmath \
        gd \
        opcache \
        pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# 先复制 vendor 和构建产物(变化少,放前面以利用镜像分层缓存)
COPY --from=vendor   /app/vendor        /app/vendor
COPY --from=frontend /app/public/build  /app/public/build

# 再复制源码(变化频繁,放最后)
COPY . /app

# Octane 启动 FrankenPHP,监听 :8000
EXPOSE 8000 80 443

ENV OCTANE_SERVER=frankenphp \
    SERVER_NAME=":8000" \
    APP_ENV=production

# 健康检查:用 Octane 的 status 命令
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD php artisan octane:status || exit 1

# 入口:frankenphp 用 Caddyfile 启动 + Octane worker
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "octane"]
