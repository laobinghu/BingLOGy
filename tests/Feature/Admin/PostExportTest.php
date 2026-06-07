<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_download_post_as_markdown(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => '我的第一篇',
            'slug' => 'my-first',
            'body' => "# Hello\n\nWorld",
            'excerpt' => '简介',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('admin.posts.export', $post));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=my-first.md', $response->headers->all()['content-disposition'][0] ?? '');

        $content = $response->streamedContent();
        $this->assertStringContainsString('title: 我的第一篇', $content);
        $this->assertStringContainsString('slug: my-first', $content);
        $this->assertStringContainsString('# Hello', $content);
    }

    public function test_other_user_cannot_download_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $post = Post::create([
            'user_id' => $owner->id,
            'title' => 'X',
            'body' => 'body',
        ]);

        $this->actingAs($other);
        $this->get(route('admin.posts.export', $post))->assertForbidden();
    }
}
