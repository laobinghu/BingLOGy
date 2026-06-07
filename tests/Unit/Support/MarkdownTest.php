<?php

namespace Tests\Unit\Support;

use App\Support\Markdown;
use PHPUnit\Framework\TestCase;

class MarkdownTest extends TestCase
{
    public function test_renders_basic_paragraph(): void
    {
        $html = Markdown::toHtml('Hello world');

        $this->assertStringContainsString('<p>Hello world</p>', $html);
    }

    public function test_renders_gfm_table(): void
    {
        $md = <<<'MD'
| a | b |
|---|---|
| 1 | 2 |
MD;

        $html = Markdown::toHtml($md);

        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('<th>a</th>', $html);
        $this->assertStringContainsString('<td>1</td>', $html);
    }

    public function test_renders_gfm_task_list(): void
    {
        $md = "- [x] done\n- [ ] todo";
        $html = Markdown::toHtml($md);

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('checked', $html);
    }

    public function test_renders_gfm_strikethrough(): void
    {
        $html = Markdown::toHtml('~~old~~');
        $this->assertStringContainsString('<del>old</del>', $html);
    }

    public function test_renders_autolink(): void
    {
        $html = Markdown::toHtml('Visit https://example.com today.');
        $this->assertStringContainsString('<a href="https://example.com">https://example.com</a>', $html);
    }

    public function test_heading_permalink_anchor_is_present(): void
    {
        $html = Markdown::toHtml("## Section\n\nBody");
        $this->assertStringContainsString('id="content-section"', $html);
        $this->assertStringContainsString('heading-permalink', $html);
    }

    public function test_disallowed_script_tag_is_escaped(): void
    {
        $html = Markdown::toHtml("<script>alert(1)</script>\n\nHello");
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_code_block_keeps_language_class(): void
    {
        $md = "```php\necho 'hi';\n```";
        $html = Markdown::toHtml($md);

        $this->assertStringContainsString('language-php', $html);
        $this->assertStringContainsString("echo 'hi';", $html);
    }

    public function test_toc_extractor_renders_toc_html(): void
    {
        $md = "## One\n\nIntro\n\n## Two\n\nMore\n";
        $toc = Markdown::toToc($md);

        $this->assertStringContainsString('class="toc-list"', $toc);
        $this->assertStringContainsString('One', $toc);
        $this->assertStringContainsString('Two', $toc);
        $this->assertStringContainsString('href="#content-one"', $toc);
        $this->assertStringContainsString('href="#content-two"', $toc);
    }
}
