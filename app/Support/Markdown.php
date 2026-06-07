<?php

namespace App\Support;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListData;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\StringContainerHelper;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;

class Markdown
{
    private static ?MarkdownConverter $converter = null;

    private static ?Environment $parserEnv = null;

    public static function toHtml(string $markdown): string
    {
        return self::converter()->convert($markdown)->getContent();
    }

    public static function toToc(string $markdown): string
    {
        $parser = new MarkdownParser(self::parserEnvironment());
        $document = $parser->parse($markdown);

        $min = 2;
        $max = 4;

        $listData = new ListData;
        $listData->type = ListBlock::TYPE_BULLET;

        $root = new ListBlock($listData);
        $root->data->set('attributes/class', 'toc-list');

        $stack = [$root];
        $prevLevel = $min - 1;

        foreach (self::headings($document) as $heading) {
            $level = $heading->getLevel();
            if ($level < $min || $level > $max) {
                continue;
            }

            $text = StringContainerHelper::getChildText($heading);
            $slug = self::slugify($text);
            $link = new Link('#content-'.$slug, $text ?: '-');
            $item = new ListItem($listData);
            $item->appendChild($link);

            while (count($stack) > 1 && $prevLevel >= $level) {
                array_pop($stack);
                $prevLevel--;
            }

            $parent = end($stack);
            if ($parent instanceof ListBlock || $parent instanceof ListItem) {
                $parent->appendChild($item);
            }

            if ($level > $prevLevel) {
                $stack[] = $item;
                $prevLevel = $level;
            }
        }

        if (! $root->hasChildren()) {
            return '';
        }

        $renderer = new HtmlRenderer(self::parserEnvironment());

        $document->prependChild($root);

        return (string) $renderer->renderDocument($document);
    }

    /**
     * @return iterable<Heading>
     */
    private static function headings(Document $document): iterable
    {
        foreach ($document->iterator() as $node) {
            if ($node instanceof Heading) {
                yield $node;
            }
        }
    }

    private static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $text) ?? '';
        $text = preg_replace('/\s+/', '-', $text) ?? '';

        return trim($text, '-_');
    }

    private static function parserEnvironment(): Environment
    {
        if (self::$parserEnv === null) {
            self::$parserEnv = new Environment([
                'heading_permalink' => [
                    'html_class' => 'heading-permalink',
                    'insert' => 'after',
                    'min_heading_level' => 2,
                    'max_heading_level' => 4,
                    'symbol' => '#',
                ],
            ]);
            self::$parserEnv->addExtension(new CommonMarkCoreExtension);
            self::$parserEnv->addExtension(new GithubFlavoredMarkdownExtension);
            self::$parserEnv->addExtension(new HeadingPermalinkExtension);
        }

        return self::$parserEnv;
    }

    private static function converter(): MarkdownConverter
    {
        if (self::$converter === null) {
            $environment = new Environment([
                'heading_permalink' => [
                    'html_class' => 'heading-permalink',
                    'insert' => 'after',
                    'min_heading_level' => 2,
                    'max_heading_level' => 4,
                    'symbol' => '#',
                    'title' => 'Permalink',
                ],
                'table_of_contents' => [
                    'min_heading_level' => 2,
                    'max_heading_level' => 4,
                    'placeholder' => '[[_TOC_]]',
                    'position' => 'placeholder',
                ],
                'disallowed_raw_html' => [
                    'disallowed_tags' => ['script', 'style', 'iframe', 'object', 'embed', 'form'],
                ],
            ]);

            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new GithubFlavoredMarkdownExtension);
            $environment->addExtension(new HeadingPermalinkExtension);
            $environment->addExtension(new TableOfContentsExtension);
            $environment->addExtension(new DisallowedRawHtmlExtension);

            self::$converter = new MarkdownConverter($environment);
        }

        return self::$converter;
    }

    private static function tocEnvironment(): Environment
    {
        $env = new Environment([
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'insert' => 'after',
                'min_heading_level' => 2,
                'max_heading_level' => 4,
                'symbol' => '#',
            ],
            'table_of_contents' => [
                'min_heading_level' => 2,
                'max_heading_level' => 4,
                'placeholder' => '[[_TOC_]]',
                'position' => 'placeholder',
            ],
        ]);
        $env->addExtension(new CommonMarkCoreExtension);
        $env->addExtension(new GithubFlavoredMarkdownExtension);
        $env->addExtension(new HeadingPermalinkExtension);
        $env->addExtension(new TableOfContentsExtension);

        return $env;
    }
}
