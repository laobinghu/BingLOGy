<?php

namespace App\Support;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

class Markdown
{
    private static ?MarkdownConverter $converter = null;

    public static function toHtml(string $markdown): string
    {
        return self::converter()->convert($markdown)->getContent();
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
                ],
            ]);

            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new HeadingPermalinkExtension);

            self::$converter = new MarkdownConverter($environment);
        }

        return self::$converter;
    }
}
