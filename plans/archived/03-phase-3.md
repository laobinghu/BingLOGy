# Phase 3 — 内容增强

## 11. 相关文章

- [ ] `Post.php` 加 `scopeRelated(Post $post, int $limit = 3)` 按标签匹配
- [ ] 无标签时 fallback：同月发布或随机
- [ ] `PostController@show` 取相关文章传视图
- [ ] `posts/show.blade.php` 文章底部显示相关卡片（2–3 篇）

## 12. 评论系统（Giscus）

- [ ] 选择启用了 Discussions 的 GitHub 仓库
- [ ] 在 `config/services.php` 加 giscus 配置
- [ ] `posts/show.blade.php` 文章底部插入 Giscus `<script>` 标签
- [ ] 配置：repo, repoId, category, mapping（pathname 或 og:title）
- [ ] 仅已发布文章显示评论区
