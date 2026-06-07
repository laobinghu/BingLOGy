<?php

namespace Tests\Unit\Support;

use App\Support\FrontMatter;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class FrontMatterTest extends TestCase
{
    public function test_split_returns_empty_meta_when_no_front_matter(): void
    {
        [$meta, $body] = FrontMatter::split("# Hello\n\nWorld");

        $this->assertSame([], $meta);
        $this->assertSame("# Hello\n\nWorld", $body);
    }

    public function test_split_parses_simple_front_matter(): void
    {
        $raw = "---\ntitle: Hello\nslug: hello\n---\n\n# Body";
        [$meta, $body] = FrontMatter::split($raw);

        $this->assertSame('Hello', $meta['title']);
        $this->assertSame('hello', $meta['slug']);
        $this->assertSame('# Body', $body);
    }

    public function test_split_handles_crlf_line_endings(): void
    {
        $raw = "---\r\ntitle: Hi\r\n---\r\nBody";
        [$meta, $body] = FrontMatter::split($raw);

        $this->assertSame('Hi', $meta['title']);
        $this->assertSame('Body', $body);
    }

    public function test_split_handles_utf8_bom(): void
    {
        $raw = "\xEF\xBB\xBF---\ntitle: Bom\n---\nBody";
        [$meta, $body] = FrontMatter::split($raw);

        $this->assertSame('Bom', $meta['title']);
        $this->assertSame('Body', $body);
    }

    public function test_normalize_coerces_string_tags(): void
    {
        [$meta] = FrontMatter::split("---\ntags: php, laravel, \"live wire\"\n---\n");

        $this->assertSame(['php', 'laravel', 'live wire'], $meta['tags']);
    }

    public function test_normalize_lowercases_keys(): void
    {
        [$meta] = FrontMatter::split("---\nTitle: A\nSlug: a\n---\n");

        $this->assertArrayHasKey('title', $meta);
        $this->assertArrayHasKey('slug', $meta);
    }

    public function test_normalize_parses_date_to_carbon(): void
    {
        [$meta] = FrontMatter::split("---\ndate: 2026-06-07 12:30\n---\n");

        $this->assertInstanceOf(Carbon::class, $meta['date']);
        $this->assertSame('2026-06-07 12:30', $meta['date']->format('Y-m-d H:i'));
    }

    public function test_normalize_does_not_misparse_iso8601_timestamp_as_unix_string(): void
    {
        [$meta] = FrontMatter::split("---\ndate: 2024-06-17T00:30:03.000Z\nupdated: 2026-01-05T03:51:03.064Z\n---\n");

        $this->assertInstanceOf(\DateTimeInterface::class, $meta['date']);
        $this->assertSame('2024-06-17', $meta['date']->format('Y-m-d'));
        $this->assertSame('2026-01-05', $meta['updated']->format('Y-m-d'));
    }

    public function test_normalize_parses_numeric_timestamp_as_unix_ts(): void
    {
        [$meta] = FrontMatter::split("---\ndate: 1718584203\n---\n");

        $this->assertInstanceOf(Carbon::class, $meta['date']);
        $this->assertSame('2024-06-17', $meta['date']->format('Y-m-d'));
    }

    public function test_normalize_coerces_published_string(): void
    {
        [$meta] = FrontMatter::split("---\npublished: true\n---\n");
        $this->assertTrue($meta['published']);

        [$meta] = FrontMatter::split("---\npublished: false\n---\n");
        $this->assertFalse($meta['published']);
    }

    public function test_join_produces_parseable_round_trip(): void
    {
        $original = [
            'title' => 'A Title',
            'slug' => 'a-title',
            'date' => Carbon::create(2026, 6, 7, 12, 0),
            'tags' => ['php', 'laravel'],
            'excerpt' => '摘要',
            'published' => true,
        ];
        $body = "# Body\n\nText";

        $raw = FrontMatter::join($original, $body);
        [$meta, $parsedBody] = FrontMatter::split($raw);

        $this->assertSame('A Title', $meta['title']);
        $this->assertSame('a-title', $meta['slug']);
        $this->assertInstanceOf(Carbon::class, $meta['date']);
        $this->assertSame(['php', 'laravel'], $meta['tags']);
        $this->assertSame('摘要', $meta['excerpt']);
        $this->assertTrue($meta['published']);
        $this->assertSame($body, $parsedBody);
    }

    public function test_join_with_empty_meta_returns_body_only(): void
    {
        $this->assertSame('Just body', FrontMatter::join([], 'Just body'));
    }

    public function test_join_preserves_extra_keys(): void
    {
        $raw = FrontMatter::join(['title' => 'T', 'meta_description' => 'desc'], 'B');
        [$meta] = FrontMatter::split($raw);

        $this->assertSame('T', $meta['title']);
        $this->assertSame('desc', $meta['meta_description']);
    }

    public function test_invalid_yaml_returns_empty_meta(): void
    {
        [$meta] = FrontMatter::split("---\ntitle: [unclosed\n---\nbody");
        $this->assertSame([], $meta);
    }
}
