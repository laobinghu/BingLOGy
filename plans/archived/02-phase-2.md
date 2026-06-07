# Phase 2 — Schema 变更 & 新模型

## 7. 标签系统

- [x] Migration: `create_tags_table`（id, name, slug）
- [x] Migration: `create_post_tag_table`（post_id, tag_id）
- [x] 新建 `app/Models/Tag.php`
- [x] `Post.php` 加 `belongsToMany(Tag::class)` + `tags()` 关系
- [x] `PostController@store` / `@update` 同步标签
- [x] 后台 create/edit 表单加多选标签
- [x] `PostController@index` 接受 `tag` 参数筛选
- [x] 加路由 `/tags/{tag}` 或 `/?tag=xxx`
- [x] 文章卡片（timeline, featured, show）显示标签 pill
- [x] 归档页头部显示所有标签列表

## 8. 阅读计数

- [x] Migration: `posts` 表加 `views` 列（`unsignedBigInteger`, default 0）
- [x] `PostController@show` 加 `$post->increment('views')`
- [x] 文章详情页显示阅读数
- [x] 时间线卡片显示阅读数（可选）

## 9. 图片上传

- [x] Migration: `posts` 表加 `cover_image` 列（nullable string）
- [x] `php artisan storage:link`
- [x] 后台 create/edit 加 file input（type=file, accept=image/\*）
- [x] `PostController@store` / `@update` 处理上传，存 `storage/app/public/covers/`
- [x] 前台特色大卡显示封面图
- [x] 前台时间线卡片显示小封面缩略图
- [x] 文章详情页顶部显示封面图

## 10. 草稿预览

- [ ] 未开始 — 需 Migration、预览路由、Controller 方法、后台预览按钮
