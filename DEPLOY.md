# BingLOGy 生产部署指南

## 1. 服务器要求

### 方式 A:Docker(推荐)

- **Docker 24+** + **Docker Compose v2**
- 域名 + 证书(Let's Encrypt 或自有)
- 可选:**Redis**(缓存/队列)、**MySQL/PostgreSQL**(数据库)
- 不需要在本机装 PHP / Node

### 方式 B:裸机部署(传统,FrankenPHP)

- **PHP 8.4+**,扩展:`pdo_sqlite`(或 `pdo_mysql`)、`zip`、`mbstring`、`openssl`、`curl`、`bcmath`、`intl`、`fileinfo`、`pcntl`
- **Composer 2.x**
- **Node 20+** + **pnpm 11+**
- **FrankenPHP**(内嵌 Caddy,提供 HTTPS 终止)
- 进程管理器:**systemd**(队列/调度器)
- 可选:**Redis**(缓存/队列)、**MySQL/PostgreSQL**(数据库)

## 2. 上传代码

### 方式 A:Docker — 拉镜像

```bash
# 拉取稳定版
docker pull ghcr.io/laobinghu/binglogy:latest
# 或者预发布版
docker pull ghcr.io/laobinghu/binglogy:nightly
```

### 方式 B:裸机 — 克隆或下载 tarball

```bash
# 方式 B-1:git 克隆
git clone <repo> /var/www/binglogy
cd /var/www/binglogy

# 方式 B-2:从 GitHub Release 下载 fat tarball(包含 vendor 和 public/build)
#  wget 或 curl 下载后解压:
tar -xzf binglogy-v0.1.0.tar.gz
cd binglogy-v0.1.0
```

## 3. 依赖安装

### 方式 A:Docker

不需要手动装依赖,镜像里已经包含 `vendor/` 和 `public/build/`。

### 方式 B:裸机

```bash
composer install --optimize-autoloader --no-dev
pnpm install --ignore-scripts && pnpm build
# 第一次部署时生成 Octane + FrankenPHP 配置
php artisan octane:install --server=frankenphp
```

## 4. 权限

```bash
sudo chown -R www-data:www-data /var/www/binglogy
sudo chmod -R 755 /var/www/binglogy
sudo chmod -R 775 storage bootstrap/cache plugins
```

## 5. `.env` 关键项

```dotenv
APP_NAME=BingLOGy
APP_ENV=production
APP_DEBUG=false
APP_URL=https://blog.example.com

APP_KEY=                       # 留空,运行 php artisan key:generate 生成

# 推荐 MySQL/PostgreSQL;SQLite 仅适合单机
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=binglogy
DB_USERNAME=binglogy
DB_PASSWORD=...

# 生产邮件(必改)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# 缓存/队列(可选 Redis)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1

# 生产存储建议改 S3/OSS/COS
STORAGE_DEFAULT_DISK=s3

LOG_CHANNEL=stack
LOG_LEVEL=warning
```

## 6. 首次部署

```bash
php artisan key:generate
php artisan storage:link          # 创建 public/storage 软链
php artisan migrate --force        # 不带 --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

## 7. 队列 + 调度(必须)

**Supervisor** `/etc/supervisor/conf.d/binglogy-worker.conf`：

```ini
[program:binglogy-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/binglogy/artisan queue:work redis --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/binglogy/worker.log
```

**Crontab**（`crontab -e`）：

```
* * * * * cd /var/www/binglogy && php artisan schedule:run >> /dev/null 2>&1
```

## 8. Web 服务器配置

### 方式 A:Docker — FrankenPHP 自带 Caddy

镜像里已经跑 FrankenPHP + Caddy,默认监听 `:8000`。**不需要单独装 nginx**。

如果你想用本机 nginx 做反代(终止 HTTPS,转发到容器),Nginx 端这样配:

```nginx
upstream binglogy {
    server 127.0.0.1:8000;
}

server {
    listen 80;
    server_name blog.example.com;
    client_max_body_size 100M;

    location / {
        proxy_pass http://binglogy;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

启用 HTTPS:

```bash
sudo certbot --nginx -d blog.example.com
```

### 方式 B:裸机 — 直接跑 FrankenPHP

Laravel 8.4+ 用 FrankenPHP 监听 :8000,内置 Caddy 提供 HTTPS:

```bash
# 启动(后台)
php artisan octane:start --server=frankenphp --port=8000 --workers=4

# 或者用 systemd(推荐)
```

`/etc/systemd/system/binglogy.service`:

```ini
[Unit]
Description=BingLOGy (FrankenPHP)
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/binglogy
ExecStart=/usr/bin/php artisan octane:start --server=frankenphp --port=8000 --workers=4
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now binglogy
```

**自动 HTTPS**(FrankenPHP 内置 Caddy,可以通过配置自动申请 Let's Encrypt 证书):

`/etc/caddy/Caddyfile`(`/etc/caddy/Caddyfile.d/binglogy.conf`):

```caddyfile
blog.example.com {
    reverse_proxy 127.0.0.1:8000
    encode zstd gzip
}
```

```bash
sudo systemctl reload caddy
```

## 9. 后续部署流程（零停机）

### 方式 A:Docker

```bash
# 拉新镜像
docker pull ghcr.io/laobinghu/binglogy:latest

# 重启容器(假设用 docker compose)
cd /opt/binglogy
docker compose up -d

# 跑迁移
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize
```

### 方式 B:裸机

```bash
cd /var/www/binglogy
git pull origin main
composer install --optimize-autoloader --no-dev
pnpm install --ignore-scripts && pnpm build
php artisan migrate --force
php artisan config:cache route:cache view:cache event:cache
php artisan storage:link      # 重复运行幂等
sudo systemctl restart binglogy   # FrankenPHP 重启加载新代码
php artisan queue:restart         # 让 worker 优雅重启
```

## 10. Flux 静态资源(可选加速)

默认 `@fluxScripts` 通过 `/flux/flux.js` 路由响应 128 KB 的 `flux-lite.min.js`，PHP 每次读盘。可选加速：

```bash
php artisan flux:install   # 复制到 public/vendor/flux/
```

随后 `@fluxScripts` 会优先用静态文件。

## 11. 备份

- 数据库：`mysqldump binglogy | gzip > backup-$(date +%F).sql.gz`（cron 每天）
- `storage/app/` 媒体文件（若用 local disk）
- `.env`（单独加密备份）

## 12. 监控检查清单

- [ ] `APP_DEBUG=false` 生效（出错显示 500 页，不含堆栈）
- [ ] `storage/logs/laravel.log` 轮转配置
- [ ] 上传目录 `plugins/` 配额 / 扫描
- [ ] 维护模式开关测试：`php artisan down --secret=...`
- [ ] 健康检查：`/up` 路由（Laravel 11+ 默认）
