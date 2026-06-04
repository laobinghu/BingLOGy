# Phase 1 — 基础设施 & 内容渲染

## 1. Markdown 支持

- [x] `composer require league/commonmark`（已通过 Laravel 依赖引入）
- [x] 新建 `app/Support/Markdown.php`：渲染 helper
- [x] 更新 `PostPresenter`：加 `bodyHtml(Post $post)` 方法
- [x] 更新 `posts/show.blade.php`：`{!! $bodyHtml !!}` 替换 `nl2br(e($body))`
- [x] 管理后台 create/edit 表单加"支持 Markdown 语法"提示

## 2. Tailwind Typography

- [x] `pnpm add -D @tailwindcss/typography`
- [x] `resources/css/app.css` 加 `@plugin "@tailwindcss/typography"`
- [x] `posts/show.blade.php` 正文容器加 `prose prose-stone dark:prose-invert`

## 3. 自定义 404

- [x] 新建 `resources/views/errors/404.blade.php`
- [x] 继承 `layouts.public`，显示"页面未找到"+返回首页链接

## 4. Sitemap

- [x] `routes/web.php` 加 GET `/sitemap.xml`
- [x] 新建 `resources/views/sitemap.blade.php` XML 模板
- [x] 查询所有已发布文章，按 updated_at 排序

## 5. RSS 全文输出

- [x] `resources/views/feed.blade.php` 每个 entry 加 `<content type="html">`
- [x] 内容用 `PostPresenter::bodyHtml($post)`

## 6. 分页优化

- [x] `resources/views/posts/index.blade.php` 改为页码分页（前后各 2 页）
- [x] 当前页高亮、鼠标悬停效果，stone 风格匹配
