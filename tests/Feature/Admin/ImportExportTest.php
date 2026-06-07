<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use App\Services\PostExporter;
use App\Services\PostImporter;
use App\Support\FrontMatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_paste_preview_redirects_to_index_with_raw(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $raw = <<<'MD'
---
title: Paste Test
slug: paste-test
---

# Body
MD;

        $response = $this->post(route('admin.import-export.preview'), ['raw' => $raw]);

        $response->assertRedirect();
        $this->assertStringContainsString('raw=', $response->headers->get('Location'));
    }

    public function test_paste_preview_rejects_empty_input(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->from(route('admin.posts.create'))
            ->post(route('admin.import-export.preview'), ['raw' => '']);

        $response->assertRedirect();
        $response->assertSessionHasErrors('raw');
    }

    public function test_importer_creates_post_as_draft_by_default(): void
    {
        $importer = app(PostImporter::class);
        $result = $importer->fromString(<<<'MD'
---
title: Drafted
slug: drafted
date: 2026-06-07 12:00
tags: php, laravel
excerpt: e
---

# Body
MD);

        $this->assertFalse($result->hasErrors());
        $this->assertSame('Drafted', $result->title());

        $user = User::factory()->create();
        $this->actingAs($user);

        [$ok, $post, $errors] = $importer->create($result);

        $this->assertTrue($ok, 'Errors: '.json_encode($errors));
        $this->assertNotNull($post);
        $this->assertSame('drafted', $post->slug);
        $this->assertNull($post->published_at, 'should default to draft');
        $this->assertCount(2, $post->tags()->get());
    }

    public function test_importer_can_create_published_post(): void
    {
        $importer = app(PostImporter::class);
        $result = $importer->fromString(<<<'MD'
---
title: Pub
slug: pub
date: 2026-06-07 12:00
published: true
---

# Body
MD);

        $user = User::factory()->create();
        $this->actingAs($user);

        [$ok, $post] = $importer->create($result, publish: true);
        $this->assertTrue($ok);
        $this->assertNotNull($post->published_at);
    }

    public function test_importer_creates_unique_slug_on_collision(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Post::create([
            'user_id' => $user->id,
            'title' => 'Existing',
            'slug' => 'exists',
            'body' => '',
        ]);

        $importer = app(PostImporter::class);
        $result = $importer->fromString(<<<'MD'
---
title: New
slug: exists
---

# Body
MD);

        [$ok, $post] = $importer->create($result);
        $this->assertTrue($ok);
        $this->assertNotSame('exists', $post->slug);
    }

    public function test_importer_reports_missing_title(): void
    {
        $importer = app(PostImporter::class);
        $result = $importer->fromString(<<<'MD'
---
slug: no-title
---

# Body
MD);

        $this->assertTrue($result->hasErrors());
    }

    public function test_importer_round_trip_via_exporter(): void
    {
        $importer = app(PostImporter::class);
        $original = <<<'MD'
---
title: Round
slug: round
date: 2026-06-07 12:00
tags: [php]
excerpt: e
---

Body
MD;

        $result = $importer->fromString($original);

        $this->actingAs(User::factory()->create());
        [$ok, $post] = $importer->create($result, publish: true);
        $this->assertTrue($ok);

        $exporter = app(PostExporter::class);
        $rendered = $exporter->render($post);

        [$meta2, $body2] = FrontMatter::split($rendered);
        $this->assertSame('Round', $meta2['title']);
        $this->assertStringContainsString('Body', $body2);
    }
}
