# BingLOGy 生产部署指南

## 1. 服务器要求

- **PHP 8.5+**，扩展：`pdo_sqlite`(或 `pdo_mysql`)、`zip`、`mbstring`、`openssl`、`curl`、`bcmath`、`intl`、`fileinfo`
- **Composer 2.x**
- **Node 20+** + **npm**(或 **Bun**)
- Web 服务器：**Nginx** + PHP-FPM，或 Apache + mod_php
- 进程管理器：**systemd** 或 **Supervisor**(队列/调度器)
- 可选：**Redis**(缓存/队列)、**MySQL/PostgreSQL**(数据库)

## 2. 上传代码

```bash
git clone <repo> /var/www/binglogy
cd /var/www/binglogy
```

## 3. 依赖安装

```bash
composer install --optimize-autoloader --no-dev
npm ci && npm run build
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

## 8. Nginx 配置

`/etc/nginx/sites-available/binglogy`：

```nginx
server {
    listen 80;
    server_name blog.example.com;
    root /var/www/binglogy/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    index index.php;
    charset utf-8;

    client_max_body_size 100M;     # 主题 ZIP / 媒体上传

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

启用 HTTPS（强烈推荐）：

```bash
sudo certbot --nginx -d blog.example.com
```

## 9. 后续部署流程（零停机）

```bash
cd /var/www/binglogy
git pull origin main
composer install --optimize-autoloader --no-dev
npm ci && npm run build
php artisan migrate --force
php artisan config:cache route:cache view:cache event:cache
php artisan storage:link      # 重复运行幂等
php artisan queue:restart     # 让 worker 优雅重启加载新代码
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
