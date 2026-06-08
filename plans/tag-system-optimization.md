# Tag System Optimization Plan

## Overview

Comprehensive optimization plan for BingLOGy's article tag system, covering 4 phases from performance to edge features.

---

## Phase 1: Core Performance Optimization (P0)

### 1.1 Create `tag_aliases` table migration

- Fields: `id`, `tag_id`(FK→tags.id, cascadeOnDelete), `alias`(string, unique index)
- Replace the current practice of storing aliases in `meta->aliases` JSON

### 1.2 Create `TagAlias` model

- File: `app/Models/TagAlias.php`
- BelongsTo `Tag` relationship

### 1.3 Refactor `Tag` model alias logic

- Add `aliases()` HasMany relationship
- `getAliasesAttribute()` reads from `tag_aliases` table
- `setAliases($aliases)` syncs `tag_aliases` table
- Migration to migrate existing alias data from `meta->aliases` to `tag_aliases`

### 1.4 Rewrite `findByAlias()` in TagService

- Current: iterates ALL tags in memory — O(n), doesn't scale
- New: `TagAlias::where('alias', $name)->first()?->tag_id` — O(1)

### 1.5 Rewrite `suggest()` in TagService

- Current: `LIKE '%query%'` on name/slug + in-memory alias search
- New: SQL `LIKE "$query%"` (prefix match) + alias subquery UNION + LIMIT 10
- Add pagination support

### 1.6 Create API endpoint for tag suggestions

- New file: `app/Http/Controllers/Api/TagSuggestionController.php`
- Route: `GET /api/tags/suggest?q=keyword`
- Returns JSON: `{id, name, slug, color, posts_count}[]`

### 1.7 Update admin post form autocomplete

- File: `resources/views/admin/posts/_form.blade.php`
- Alpine.js: remove `Js::from($allTags)`, switch to `fetch('/api/tags/suggest?q=...')`
- Add debounce (300ms) on autocomplete input
- Remove full tag list loading from PostController create/edit views

---

## Phase 2: Enhancement (P1)

### 2.1 Create `tag_analytics` table

- Fields: `id`, `tag_id`(FK→tags.id, cascadeOnDelete), `period`(string: daily/weekly/monthly), `post_count`(int), `searches_count`(int), `period_start`(date)
- Composite unique index: `(tag_id, period, period_start)`

### 2.2 Create `TagAnalytics` model

- File: `app/Models/TagAnalytics.php`

### 2.3 Create `TagAnalyticsService`

- File: `app/Services/TagAnalyticsService.php`
- Methods: `recordSearch(tagId)`, `getTrending(period, limit)`, `getRelatedTags(tagId)` (co-occurrence analysis)

### 2.4 Create `TagDeduplicator` service

- File: `app/Services/TagDeduplicator.php`
- Scan for similar names using Levenshtein distance (< 3) or common substring matching
- Return suggested merge pairs

### 2.5 Create Artisan command `tags:detect-duplicates`

- File: `app/Console/Commands/DetectDuplicateTags.php`
- Calls `TagDeduplicator`, outputs report with merge suggestions

### 2.6 Tag cloud dual-layer caching

- Update `TagObserver` cache invalidation
- Level 1: `tag_cloud.hot` (top 20 by posts_count, 5min TTL)
- Level 2: `tag_cloud.all` (all tags, 30min TTL, cleared on tag CRUD)

### 2.7 `syncAllCounts()` incremental mode

- Add `--since=` option for incremental sync
- Add `--chunk=1000` option for batch processing

---

## Phase 3: Architecture & UX Improvements (P2-P3)

### 3.1 Split `TagController` into Admin + Public

- `AdminTagController` (CRUD + merge)
- `PublicTagController` (show)
- Update routes accordingly

### 3.2 Wrap merge operation in DB transaction

- `TagService::merge()` → `DB::transaction(function() { ... })`

### 3.3 Drag-and-drop sorting in admin tag list

- Integrate Sortable.js
- Add `POST /admin/tags/reorder` endpoint

### 3.4 Enhanced tag validation

- Add name format validation (no pure numbers/special chars)
- Add slug format validation

### 3.5 XSS safety review

- Ensure all tag output uses proper escaping

---

## Phase 4: Edge Features (P3-P4)

### 4.1 Tag page SEO

- Auto-generate `<meta name="description">` from tag description
- Add `<link rel="canonical">`
- Add Open Graph tags

### 4.2 Tag RSS Feed

- Route: `GET /tags/{slug}/feed`
- Returns RSS/Atom XML of posts for that tag

### 4.3 Admin tag stats panel

- Cards showing: total tags, tags with posts, trending tags

### 4.4 Related tags display

- Show "often used together" tags on public tag page
