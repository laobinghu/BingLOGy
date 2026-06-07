<?php

namespace App\Services;

use Illuminate\Support\Str;

class CommentModerationService
{
    public static function classify(string $body, ?string $ipAddress = null): string
    {
        $sensitive = self::getSensitiveWords();
        foreach ($sensitive as $word) {
            if ($word !== '' && Str::contains($body, $word)) {
                return 'spam';
            }
        }

        $linkLimit = (int) self::setting('comment_link_limit', 2);
        $linkCount = preg_match_all('#https?://\S+#i', $body);
        if ($linkCount > $linkLimit) {
            return 'spam';
        }

        $interval = (int) self::setting('comment_ip_interval_seconds', 60);
        if ($ipAddress && $interval > 0) {
            $recent = \App\Models\Comment::where('ip_address', $ipAddress)
                ->where('created_at', '>=', now()->subSeconds($interval))
                ->exists();
            if ($recent) {
                return 'spam';
            }
        }

        return self::setting('comment_require_approval', true) ? 'pending' : 'approved';
    }

    protected static function getSensitiveWords(): array
    {
        $words = self::setting('comment_sensitive_words', []);

        return is_array($words) ? $words : [];
    }

    protected static function setting(string $key, mixed $default = null): mixed
    {
        return SettingsManager::get($key, $default);
    }
}
