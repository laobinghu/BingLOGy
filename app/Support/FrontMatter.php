<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Symfony\Component\Yaml\Yaml;

class FrontMatter
{
    public const DELIMITER = '---';

    public const KEYS = [
        'title',
        'slug',
        'date',
        'tags',
        'excerpt',
        'published',
        'cover_image',
    ];

    /**
     * Split a markdown document into [metadata, body].
     *
     * @return array{0: array<string, mixed>, 1: string}
     */
    public static function split(string $raw): array
    {
        $raw = self::stripBom(self::normalizeLineEndings($raw));
        $lines = explode("\n", $raw);

        if (count($lines) < 2 || trim($lines[0]) !== self::DELIMITER) {
            return [[], $raw];
        }

        $closeIndex = null;
        for ($i = 1, $n = count($lines); $i < $n; $i++) {
            if (trim($lines[$i]) === self::DELIMITER) {
                $closeIndex = $i;
                break;
            }
        }

        if ($closeIndex === null) {
            return [[], $raw];
        }

        $yamlBlock = implode("\n", array_slice($lines, 1, $closeIndex - 1));
        $body = implode("\n", array_slice($lines, $closeIndex + 1));
        $body = ltrim($body, "\n\r");

        try {
            $parsed = Yaml::parse($yamlBlock, Yaml::PARSE_DATETIME) ?? [];
        } catch (\Throwable) {
            $parsed = [];
        }

        if (! is_array($parsed)) {
            $parsed = [];
        }

        return [self::normalize($parsed), $body];
    }

    /**
     * Join metadata and body back into a single markdown document.
     */
    public static function join(array $meta, string $body): string
    {
        $meta = self::normalize($meta);
        $meta = self::stringifyDates($meta);
        $meta = self::prune($meta);

        $reserved = array_intersect_key($meta, array_flip(self::KEYS));
        $extra = array_diff_key($meta, $reserved);

        $ordered = array_merge($reserved, $extra);

        if (empty($ordered)) {
            return $body;
        }

        $yaml = Yaml::dump($ordered, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_NULL_AS_TILDE);

        return rtrim(self::DELIMITER)."\n".rtrim($yaml)."\n".self::DELIMITER."\n".$body;
    }

    /**
     * Drop empty/null front matter values to keep the YAML clean.
     *
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private static function prune(array $meta): array
    {
        $out = [];
        foreach ($meta as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (is_array($value) && empty($value)) {
                continue;
            }
            if (is_string($value) && trim($value) === '') {
                continue;
            }
            $out[$key] = $value;
        }

        return $out;
    }

    /**
     * Normalize a parsed metadata array:
     *  - lowercase keys
     *  - date string/datetime → Carbon
     *  - tags (string with commas) → array
     *  - published (string 'true'/'false'/'yes'/'no') → bool
     *
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function normalize(array $meta): array
    {
        $out = [];
        foreach ($meta as $key => $value) {
            $lower = strtolower((string) $key);
            $out[$lower] = $value;
        }

        if (! isset($out['date'])) {
            // nothing to normalize
        } elseif (! $out['date'] instanceof \DateTimeInterface) {
            try {
                if (is_int($out['date']) || (is_string($out['date']) && ctype_digit(ltrim($out['date'], '-')))) {
                    $out['date'] = Carbon::createFromTimestamp((int) $out['date']);
                } else {
                    $out['date'] = Carbon::parse((string) $out['date']);
                }
            } catch (\Throwable) {
                unset($out['date']);
            }
        } elseif (! $out['date'] instanceof CarbonImmutable) {
            $out['date'] = CarbonImmutable::createFromInterface($out['date']);
        }

        if (isset($out['tags'])) {
            if (is_string($out['tags'])) {
                $out['tags'] = array_values(array_filter(array_map(
                    fn ($t) => self::unquote(trim($t)),
                    preg_split('/[,，]/u', $out['tags']) ?: []
                )));
            } elseif (is_array($out['tags'])) {
                $out['tags'] = array_values(array_map(
                    fn ($t) => self::unquote(trim((string) $t)),
                    $out['tags']
                ));
            } else {
                $out['tags'] = [];
            }
        }

        if (isset($out['published'])) {
            if (is_string($out['published'])) {
                $out['published'] = in_array(strtolower(trim($out['published'])), ['true', '1', 'yes', 'on'], true);
            } else {
                $out['published'] = (bool) $out['published'];
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private static function stringifyDates(array $meta): array
    {
        foreach ($meta as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $meta[$key] = $value->format('Y-m-d H:i');
            }
        }

        return $meta;
    }

    private static function normalizeLineEndings(string $raw): string
    {
        return str_replace(["\r\n", "\r"], "\n", $raw);
    }

    private static function stripBom(string $raw): string
    {
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            return substr($raw, 3);
        }

        return $raw;
    }

    private static function unquote(string $value): string
    {
        $len = strlen($value);
        if ($len >= 2) {
            $first = $value[0];
            $last = $value[$len - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    /**
     * Render a `<ul>` table of contents block from front matter meta (best-effort).
     */
    public static function summarize(array $meta): array
    {
        $meta = self::normalize($meta);

        $summary = [
            'title' => isset($meta['title']) ? (string) $meta['title'] : null,
            'slug' => isset($meta['slug']) ? (string) $meta['slug'] : null,
            'date' => isset($meta['date']) && $meta['date'] instanceof \DateTimeInterface
                ? $meta['date']->format('Y-m-d H:i')
                : null,
            'tags' => isset($meta['tags']) && is_array($meta['tags']) ? $meta['tags'] : [],
            'excerpt' => isset($meta['excerpt']) ? (string) $meta['excerpt'] : null,
            'published' => $meta['published'] ?? null,
            'cover_image' => isset($meta['cover_image']) ? (string) $meta['cover_image'] : null,
        ];

        $extra = array_diff_key($meta, array_flip(array_keys($summary)));

        return ['summary' => $summary, 'extra' => $extra];
    }
}
