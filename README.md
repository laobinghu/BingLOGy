# BingLOGy

> 记录建造、写作，以及那些还在进行中的事。
> A quiet place for posts, dev notes, half-finished ideas, and the small things that slowly become useful.

BingLOGy 是一个**自托管的个人/小团队博客系统**,基于 **Laravel 13 + Livewire 4 + Flux 2 + Tailwind 4**。它的目标不是取代 WordPress / Ghost / Hexo,而是给你一个**完全控制**的、写起来不卡、迁起来不难的写作环境。

---

## ✨ 特性

### 写作体验

- **Milkdown Markdown 编辑器**:ProseMirror 内核、GFM、代码高亮(8 常用 + 6 罕见语言懒加载)
- **Slash 菜单**、**Bubble 菜单**、**块拖拽手柄**
- **暗色模式**、键盘导航、所见即所得
- 文章内 `code block` 自动高亮、可一键复制

### 内容管理

- **Front Matter 导入/导出**:`Post ↔ .md` 双向同步,可批量备份、可迁库
- **标签系统**、**阅读计数**、**封面图**
- **Markdown 服务端渲染**:`Markdown.php` + `league/commonmark` 安全渲染(含 TOC、表格、任务列表)
- **ATOM Feed**、**Sitemap**、**结构化数据**

### 用户与互动

- **评论系统**:支持回复/嵌套,后端审核服务
- **2FA / Passkey (WebAuthn)**:可无密码登录
- **后台侧边栏菜单**:`config/admin-menu.php` 配置化,支持插件注入新菜单项

### 扩展性

- **插件系统**:`HookManager` + `PluginManager`,插件以 `plugins/<name>/` 形式放入即可
- **设置中心**:KV 存储的全局设置,运行时热改
- **存储策略 (Storage Strategies)** + **上传策略 (Upload Policies)**:可按文章粒度限制上传/分发
- **多主题支持**:`resources/views/themes/` 目录可放自定义主题

### 部署与运维

- 一键 `php artisan down/up` 维护模式,支持带 secret 的"内部通道"
- 健康检查路由 `/up`(Laravel 11+ 内建)
- 详细生产部署指南见 [`DEPLOY.md`](./DEPLOY.md)

---

## 🧱 技术栈

| 类别      | 选型                                                              |
| --------- | ----------------------------------------------------------------- |
| 后端框架  | PHP 8.3+ / Laravel 13                                             |
| 前端栈    | Livewire 4 + Flux 2 + Tailwind 4 + Alpine.js                      |
| 资源构建  | Vite-plus (`vp build` / `vp dev` / `vp check`)                    |
| Markdown  | [Milkdown 7](https://milkdown.dev) (ProseMirror) + `highlight.js` |
| 认证      | Laravel Fortify + `laravel/passkeys` (WebAuthn)                   |
| 数据库    | SQLite(默认) / MySQL / PostgreSQL                                 |
| 队列/缓存 | `sync` / `database` / `redis` / `file` 任选                       |

---

## 🚀 快速开始

### 环境要求

- PHP **8.3+**(扩展:`pdo_sqlite` 或 `pdo_mysql`、`mbstring`、`openssl`、`curl`、`intl`、`fileinfo`)
- Node.js **20+** + Vite-plus(`npm i -g vite-plus` 或 `vp env doctor` 自检)
- Composer 2.x

### 安装

```bash
git clone https://github.com/<your-name>/BingLOGy.git
cd BingLOGy

composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build

php artisan serve
```

默认管理员(由 seeder 创建):

- 邮箱:`admin@test.local`
- 密码:`password`

> ⚠️ 上线前请立即修改默认账号与 `APP_KEY`,并禁用 `--seed`。

### 开发模式

```bash
npm run dev    # 启动 vite-plus 监听
php artisan serve
```

`vp check` 会跑 `oxlint` + TypeScript 类型检查,可在保存时自动修复。

---

## 📁 项目结构

```
BingLOGy/
├── app/
│   ├── Http/Controllers/      # 公共路由控制器 (Post, Comment, ...)
│   ├── Livewire/Admin/        # 后台 Livewire 组件
│   │   ├── BlogSettings.php
│   │   ├── Comments/
│   │   ├── ImportExport/
│   │   ├── Plugins/
│   │   ├── StorageStrategies/
│   │   └── UploadPolicies/
│   ├── Models/                # Post, Tag, Comment, Setting, ...
│   ├── Services/              # HookManager, PluginManager, SettingsManager, ...
│   └── Support/               # FrontMatter, Markdown
├── plugins/                   # 自定义插件(运行时加载)
├── resources/
│   ├── js/
│   │   ├── editor.js          # Milkdown 编辑器
│   │   ├── highlight.js       # 代码高亮
│   │   └── shims/             # 第三方 shim(如 lodash-es)
│   ├── css/app.css
│   └── views/
│       ├── admin/             # 后台页面
│       ├── livewire/          # Livewire 组件模板
│       ├── posts/             # 公共文章页
│       └── themes/            # 自定义主题
├── config/
│   ├── admin-menu.php         # 后台侧边栏菜单
│   └── livewire.php
├── tests/
│   ├── Unit/Support/          # FrontMatterTest, MarkdownTest
│   └── Feature/Admin/         # ImportExportTest, PostExportTest
└── DEPLOY.md                  # 生产部署指南(Nginx, Supervisor, Redis)
```

---

## 🧪 测试

```bash
./vendor/bin/phpunit            # 全部测试
./vendor/bin/pint               # 代码风格(PHP)
npm run build                   # 构建前端(vp check 会自动跑)
```

测试覆盖:Front Matter 拆分/拼接、Markdown 渲染、单文章导出、批量导入/导出、认证、profile 更新等。

---

## 📜 部署

生产环境部署详见 [`DEPLOY.md`](./DEPLOY.md):包含 PHP-FPM + Nginx 配置、Supervisor worker、Redis、备份策略、Certbot HTTPS 等。

---

## 🤝 贡献

Issues 与 PR 都欢迎。请确保:

- 通过 `vendor/bin/pint`(PHP)与 `npm run build`(JS)检查
- 新增功能附带 Feature/Unit 测试
- 提交前用 `php artisan test` 跑过

---

## 📄 许可证

MIT — 详见 [LICENSE](LICENSE)。

---

> Built with Laravel · Livewire · Flux · Milkdown · Tailwind
