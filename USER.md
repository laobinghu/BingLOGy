# BingLOGy 用户手册

> 面向**博客作者 / 管理员**的日常操作指南。覆盖写文章、管理评论、配插件、做备份等。
> 跟 `README.md`（项目介绍）和 `DEPLOY.md`（生产部署）互不重叠。

---

## 0. 文档约定

- 路径：`/admin/...` 表示后台路径，`/settings/...` 表示个人设置
- 默认管理员账号（首次安装由 seeder 创建，**上线前必须改**）：
  - 邮箱：`admin@test.local`
  - 密码：`password`
- 数据库迁移和 seed：`php artisan migrate --seed`（**生产不要带 `--seed`**）

---

## 1. 账号 & 个人资料

### 1.1 登录 / 注册

- 登录：`/login`
- 注册：`/register`（如果 `config/fortify.php` 启用了 `registration`）
- 找回密码：`/forgot-password`

### 1.2 个人资料

`/settings/profile`（来自 `routes/settings.php`）

- 姓名（`name`）
- 邮箱（`email`，改邮箱会触发验证邮件）
- 头像（默认从 Gravatar 取，可改 `app/Models/User.php` 替换）

### 1.3 外观

`/settings/appearance`

- 浅色 / 深色 / 跟随系统
- 设置存到 localStorage + cookie，刷新即生效

### 1.4 安全

`/settings/security`（需密码二次确认）

- **修改密码**
- **两步验证（2FA）**：用 Authy / 1Password / Google Authenticator 扫码
- **Passkey（WebAuthn）**：用设备指纹 / Windows Hello / iCloud Keychain / 1Password 等无密码登录
  - 添加：`Add Passkey` → 系统弹窗 → 命名保存
  - 删除：列表里点垃圾桶
  - 启用后下次登录可"用 Passkey 登录"

---

## 2. 后台总览（仪表盘）

`/admin`

显示：

- 文章总数 / 已发布 / 草稿或定时
- 最近 5 篇（按创建时间）

> 数据范围只看你自己的文章（`Post::where('user_id', auth()->id())`）。
> 想看全站的要直接进文章管理列表。

---

## 3. 写文章

### 3.1 文章管理列表

`/admin/posts`

- 表格列：标题 / 状态 / 发布时间 / 操作（编辑、预览、删除、下载 .md）
- 顶部右侧 **+ 新建文章** 按钮

### 3.2 字段说明（`/admin/posts/create` 和 `/admin/posts/{slug}/edit`）

| 字段 | 必填 | 说明 |
|---|---|---|
| 标题 | ✓ | `<input>`，单行 |
| Slug | ✗ | 留空自动从标题生成（`Str::slug`）；填了会校验唯一 |
| 摘要 | ✗ | 列表页和 OG 卡片用；留空会从正文截取 |
| 封面图 | ✗ | jpeg / png / webp，**最大 2 MB**；上传走 UploadPolicy |
| 正文 | ✓ | Markdown（Milkdown 编辑器，详见 §3.3） |
| 标签 | ✗ | **两种方式二选一**（CSV 优先，会覆盖多选）：<br>① 标签 CSV：`php, laravel, livewire`（中英文逗号都行，存在的会复用，不存在的自动创建）<br>② 现有标签多选：勾选已有 tag |
| 状态 | | **三态**：<br>· 勾「已发布」→ `published_at = now()`<br>· 填「定时发布时间」→ 未来时间，cron 到点自动生效（实际是访问时再判断，所以是"懒发布"）<br>· 都不勾 → 草稿（`published_at = null`） |

校验规则（来自 `PostController::store/update`）：

- `title`: required, max:255
- `slug`: nullable, max:255, unique（编辑时排除自己）
- `body`: required
- `cover_image`: nullable, image, mimes:jpeg,png,webp, max:2048 KB

### 3.3 Milkdown 编辑器

基于 [Milkdown 7](https://milkdown.dev) (ProseMirror 内核)，所见即所得。

**特性**：

- **Slash 菜单**：在新行输入 `/` 弹出，可选标题、列表、引用、代码块、表格等
- **Bubble 菜单**：选中文本浮现，可加粗 / 斜体 / 链接 / 行内代码
- **块拖拽手柄**：鼠标悬停在块左侧出现的 ⋮⋮ 图标可拖动整块
- **自动配对**：括号、引号、`*` 配对
- **代码块** 高亮（8 常用立即 + 6 罕见懒加载，见 §3.4）
- **TOC 占位符**：正文里写 `[[_TOC_]]`，前端渲染时自动替换为目录（基于 `league/commonmark` 解析 H1–H6）
- **键盘**：`Ctrl/Cmd+B` 粗体、`Ctrl/Cmd+I` 斜体、`Ctrl/Cmd+K` 链接

### 3.4 代码高亮语言

立即加载（`resources/js/editor.js` 顶部 `import`）：

`javascript` / `typescript` / `php` / `bash` / `json` / `xml`(html) / `css` / `markdown`

懒加载（首次遇到 `\`\`\`lang` 时 `import()`）：

`python` / `sql` / `yaml` / `ini` / `go` / `rust`

其它语言不被识别时按纯文本显示。需要新语言就改 `editor.js` 加到 `LAZY_LANG_LOADERS`，然后 `vp build`。

### 3.5 单篇 Markdown 导出

编辑页右上 **下载 .md** 按钮（`/admin/posts/{post}/export`）。

导出格式（来自 `App\Services\PostExporter`）：

```markdown
---
title: 文章标题
slug: my-post-slug
excerpt: 摘要（如果有）
published_at: 2026-06-07T15:30:00+00:00
cover_image: covers/abc123.jpg
tags:
  - php
  - laravel
---

正文 Markdown...
```

> 字段顺序按字母序，front matter 解析由 `App\Support\FrontMatter` 负责（同样有 `phpunit` 测试覆盖）。
> 这个格式就是导入功能的输入格式（§8）。

### 3.6 预览

编辑页右上 **预览** 按钮：在新标签页打开前台文章页（`/posts/{slug}`）。

- 草稿打开会 404（`abort_if(is_null($post->published_at) || $post->published_at->isFuture(), 404)`），想预览草稿就把「已发布」勾上
- 预览会触发 `views` +1（`PostController::show` 里 `increment('views')`），开发期嫌烦可以临时关掉

---

## 4. 标签

`/admin/tags`

- 创建：`+ 新建标签` → 填 `name`（slug 自动）
- 重命名：点编辑改 `name`
- 删除：垃圾桶 → 二次确认 → **会同步移除所有文章的关联**（中间表 `post_tag` 里的行被清掉，文章本身不删）

> 标签 slug 用来生成 `/posts?tag=xxx` 过滤路径，重命名后 slug 可能变（取决于 `Tag` 模型的 `saving` 钩子实现），老的社交分享链接会失效，需要 301。

---

## 5. 媒体 & 上传策略

两套配置，**配合使用**：

| 资源 | 路径 | 作用 |
|---|---|---|
| **Storage Strategies**（存储策略） | `/admin/storage-strategies` | 物理位置：`local` / `s3` / `oss` / `cos` / `wasabi` 等。配一次后所有 Upload Policy 可引用 |
| **Upload Policies**（上传策略） | `/admin/upload-policies` | 谁能上传（角色 / 公开）、上传什么（mime / 大小 / 字段名）、存哪个 Storage Strategy |

逻辑：上传时按字段名（比如 `cover_image`）匹配对应 Policy → Policy 决定走哪个 Storage Strategy → 落盘后返回相对路径写回 DB。

新建博客时 `UploadPolicySeeder` 会建一个默认策略（`cover_image` 字段，本地磁盘）。**生产环境**改成 S3 / OSS / COS：

1. 先去 `config/filesystems.php` 加磁盘配置（或在 `.env` 里设凭据）
2. `/admin/storage-strategies` 建一个 S3 策略
3. `/admin/upload-policies` 编辑 `cover_image` 策略，把 storage 改成新建的 S3 策略
4. 重新上传一次封面图验证

> 插件里的上传、未来的"插图上传"等也会走同一套 Policy，新字段建新 Policy。

---

## 6. 评论

### 6.1 访客视角

文章页底部有评论表单，字段：

- 昵称（必填）
- 邮箱（必填，不公开）
- 网址（可选，**填了会显示成链接**，要小心 spam）
- 评论内容（必填）
- 父评论 ID（点「回复」自动填）

提交走 `POST /posts/{post}/comments`（来自 `CommentController::store`）。提交后**默认未审核**，等管理员在后台批准后才公开显示。

### 6.2 管理视角

`/admin/comments`

- 列表：评论内容 / 作者 / 所属文章 / 状态（待审 / 已批准 / 已拒绝） / 时间
- 操作：通过 / 拒绝 / 删除 / 标垃圾
- 嵌套显示父→子关系

> 评论的状态字段在 `comments` 表，模型 scope `approved()` 在前台渲染时过滤未批准的。

---

## 7. 博客设置

`/admin/settings/blog`（`App\Livewire\Admin\BlogSettings`）

底层是 `App\Services\SettingsManager` + `settings` 表（KV 存储，运行时热改，不需要 `config:cache`）。

常见的 key（具体看 `BlogSettings.php`）：

- `blog_name` / `blog_description` / `blog_logo`（站点元数据）
- `posts_per_page`（列表分页，**前台立即生效**）
- `comments_enabled`（全局开关评论）
- `default_theme`（默认主题名）

新增设置项：在 `BlogSettings.php` 加字段 + `SettingsManager::set()` 写默认即可。

---

## 8. 导入 / 导出

`/admin/import-export`（`App\Livewire\Admin\ImportExport\Index` + `App\Http\Controllers\PostImportExportController`）

### 8.1 批量导出（zip）

- 入口：列表页 **导出全部** 按钮 → 下载 `posts-YYYYMMDD-HHMMSS.zip`
- 范围：
  - 默认只导自己的（`user_id = auth()->id()`）
  - 加 `?all=1` 参数导所有（需要管理员角色 `admin`，否则 403）
  - 加 `?ids=1,2,3` 导指定 ID

### 8.2 单篇导出

`/admin/posts/{post}/export` → 下载单篇 `.md`（含 front matter，§3.5）

### 8.3 导入

`/admin/import-export/index`，两个标签：

- **粘贴**：直接把 §3.5 那种格式的 markdown 贴进 textarea → **预览** → 看到解析后的标题 / slug / 标签 / 发布时间 / 正文 → 确认入库
- **上传**：拖一个或多个 `.md` 文件进来 → 同样预览 → 批量确认

冲突处理：slug 重复会提示（`unique:posts,slug` 校验失败）。要么改 slug，要么在导入前删除冲突文章。

> 导入是 upsert 还是只 insert 看 `PostImporter` 实现（具体逻辑以代码为准；导入前建议先备份 DB）。

---

## 9. 插件

`/admin/plugins`（`App\Livewire\Admin\Plugins\Index` + `App\Services\PluginManager`）

### 9.1 安装

把插件文件夹丢进 `plugins/<plugin-name>/`，目录里需要：

```
plugins/<plugin-name>/
├── plugin.php              # 必需：name, version, hooks 注册, 路由注册等
├── composer.json           # 可选：声明额外依赖
└── ...
```

后台 **刷新** 按钮（或在配置 cache 失效后）会扫描 `plugins/` 目录，新插件出现在列表里。

### 9.2 启用 / 禁用

- 每个插件的「启用」开关：写到 `plugin_states` 表（`plugin_name`, `enabled`）
- 禁用后插件的 hooks 不注册、路由不加载（视实现而定）
- 卸载 = `rm -rf plugins/<name>` + 后台「卸载」清掉 `plugin_states` 行

### 9.3 钩子（Hooks）

来自 `App\Services\HookManager`，插件可在 `plugin.php` 里 `HookManager::on('post.saved', fn ($post) => ...)` 订阅事件。

常用 hook 点（具体清单以 `HookManager` 源码为准）：

- `post.saved` / `post.published` / `post.deleted`
- `comment.submitted` / `comment.approved`
- `admin.menu.render`（注入左侧菜单项）
- `markdown.render`（改渲染管线）
- `settings.saved`

### 9.4 示例

仓库自带一个 `plugins/hello-hook/`（参考用，列出可用 hook 点）。

---

## 10. 公开页面

| 路径 | 名称 | 说明 |
|---|---|---|
| `/` | 首页 | 最近 1 篇 featured + 6 篇列表（`Post::limit(7)`，来自 `routes/web.php` 首页闭包） |
| `/posts` | 列表 | 全部已发布，按 `published_at desc`，分页（`posts_per_page` 控制） |
| `/posts?tag=xxx` | 按 tag 过滤 | 用 tag 的 slug |
| `/posts/{slug}` | 单篇 | 渲染 Markdown → HTML，触发 `views` +1，加载已批准评论 |
| `/feed` | ATOM Feed | 最近 20 篇，application/atom+xml |
| `/sitemap.xml` | Sitemap | 全部已发布 |
| `/up` | 健康检查 | Laravel 11+ 内建，返回 200 |

**评论提交**：`POST /posts/{post}/comments`（`comments.store`）

> 公开页面受 `check.maintenance` 中间件保护，进入维护模式时（`php artisan down`）访客会看到 503 + 维护页（除非带 `?secret=...` 免维护通道）。

---

## 11. 键盘快捷键

编辑器内（Milkdown）：

| 快捷键 | 行为 |
|---|---|
| `Ctrl/Cmd + B` | 粗体 |
| `Ctrl/Cmd + I` | 斜体 |
| `Ctrl/Cmd + K` | 插入链接 |
| `Ctrl/Cmd + Z` / `Shift+Z` | 撤销 / 重做 |
| `Ctrl/Cmd + Shift + V` | 粘贴为纯文本 |
| `/`（行首） | 打开 slash 菜单 |
| ``` ``` `` + 空格 | 切换代码块 |
| `#` + 空格 | 一级标题 |
| `##` + 空格 | 二级标题 |
| `-` / `*` + 空格 | 无序列表 |
| `1.` + 空格 | 有序列表 |
| `> ` | 引用 |
| `---` | 分割线 |

后台：

- `g` `p` → 跳到文章列表（如果实现了 navigation 快捷键）
- `Esc` → 关闭弹窗

---

## 12. 常见问题

### Q：默认管理员密码忘了，进不去后台怎么办？

SSH 到服务器，进 tinker 重置：

```bash
php artisan tinker
>>> $u = App\Models\User::where('email', 'admin@test.local')->first();
>>> $u->password = Illuminate\Support\Facades\Hash::make('new-password');
>>> $u->save();
>>> exit
```

如果是 2FA / Passkey 锁了，先用数据库把这俩关掉：

```php
$u->two_factor_secret = null;
$u->two_factor_recovery_codes = null;
$u->two_factor_confirmed_at = null;
$u->save();

// Passkey 整张表清掉
DB::table('passkeys')->where('authenticatable_id', $u->id)->delete();
```

### Q：怎么换主题？

主题是 Blade 模板，放进 `resources/views/themes/<name>/`，结构跟 `resources/views/posts/` 一致（`index.blade.php` / `show.blade.php` / `partials/`）。在博客设置里改 `default_theme` 即可。

### Q：怎么备份？

最小三件套：

```bash
# 1. 数据库（按你用的）
mysqldump -u root -p binglogy | gzip > db-$(date +%F).sql.gz
# SQLite 直接 cp
cp database/database.sqlite backup-$(date +%F).sqlite

# 2. 上传文件（用了 local disk 的话）
tar -czf storage-$(date +%F).tar.gz storage/app/

# 3. .env（单独存，包含密钥）
cp .env env-$(date +%F).bak
```

挂 cron 每天跑，异地同步一份。

### Q：怎么迁移到新服务器？

1. 旧服务器 dump DB + 打包 `storage/app/` + `.env`
2. 新服务器 `git clone`（或拉 Docker 镜像 `docker pull ghcr.io/laobinghu/binglogy:latest`）
3. 恢复 DB / storage / `.env`
4. 跑 `php artisan storage:link`
5. 详细见 `DEPLOY.md`

### Q：Octane / FrankenPHP 是干嘛的？为什么不直接用 PHP-FPM？

- Octane 把 PHP 进程常驻内存，请求来了直接复用，**省掉 bootstrap 开销**，单机能扛更高 QPS
- FrankenPHP 用 Caddy 做 HTTP 服务器，**内置 HTTPS 终止**（自动 Let's Encrypt）、HTTP/2 / HTTP/3、Early Hints
- 副作用：每次部署后必须重启 Octane workers（`php artisan octane:reload`），代码不立即生效
- 详细见 `DEPLOY.md` §8

### Q：访问 502 / 500 怎么排查？

1. `php artisan octane:status` 看 worker 是否在跑
2. `tail -f storage/logs/laravel.log` 看异常堆栈
3. `.env` 临时改 `APP_DEBUG=true` 重启，立刻看到详细错误（**别在生产开 debug 太久**）
4. 公开 `/up` 路由：能 200 说明 Laravel 起来了；前页 502 多半是 Octane 挂了

### Q：怎么禁用注册？

`config/fortify.php`：

```php
'features' => [
    // ...
    Features::registration(),   // 注释掉这行
    // ...
],
```

清 config cache：`php artisan config:clear`。

---

## 13. 维护模式

```bash
# 进维护（带内部免维护 secret）
php artisan down --secret=my-secret-123

# 访客：https://blog.example.com  → 503 维护页
# 你：https://blog.example.com?secret=my-secret-123  → 正常浏览，cookie 记住 1 小时

# 退出
php artisan up
```

`DEPLOY.md` §12 提到了——配合 Octane 部署（先 `php artisan octane:reload`，再 `up`）能做到零停机。

---

## 14. 下一步

- **生产部署**（nginx / Docker / 监控 / 备份 cron）→ [`DEPLOY.md`](./DEPLOY.md)
- **项目结构 & 开发指南**（插件开发、主题开发、贡献流程）→ [`README.md`](./README.md) §"项目结构"
- **自动化发布 / 部署编排**（计划中，会引入 Deployer + atomic releases）→ 待补充
