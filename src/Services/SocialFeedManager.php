<?php

namespace MiPress\SocialFeeds\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MiPress\SocialFeeds\Contracts\SocialProvider;
use MiPress\SocialFeeds\Enums\SocialPlatform;
use MiPress\SocialFeeds\Models\SocialFeed;
use MiPress\SocialFeeds\Models\SocialPost;
use MiPress\SocialFeeds\Providers\FacebookProvider;

class SocialFeedManager
{
    public function resolve(SocialPlatform $platform): SocialProvider
    {
        return match ($platform) {
            SocialPlatform::Facebook => app(FacebookProvider::class),
            default => throw new \InvalidArgumentException(
                "Provider pro platformu [{$platform->value}] není implementován."
            ),
        };
    }

    public function getFeedData(SocialFeed $feed): Collection
    {
        if (! $feed->is_active) {
            return collect();
        }

        $store = config('social-feeds.cache.store');
        $cacheStore = Cache::store($store);

        $cached = $cacheStore->get($feed->cacheKey());

        if ($cached instanceof Collection) {
            return $this->applyFilters($feed, $cached);
        }

        // Cache miss or corrupted entry (e.g. __PHP_Incomplete_Class)
        $cacheStore->forget($feed->cacheKey());

        $data = $this->fetchAndPersist($feed);

        $cacheStore->put($feed->cacheKey(), $data, $feed->cache_ttl);

        return $this->applyFilters($feed, $data);
    }

    public function refreshFeed(SocialFeed $feed): Collection
    {
        $store = config('social-feeds.cache.store');
        Cache::store($store)->forget($feed->cacheKey());

        return $this->getFeedData($feed);
    }

    public function clearCache(SocialFeed $feed): void
    {
        $store = config('social-feeds.cache.store');
        Cache::store($store)->forget($feed->cacheKey());
    }

    private function fetchAndPersist(SocialFeed $feed): Collection
    {
        $feed->loadMissing('account');
        $account = $feed->account;

        if (! $account || $account->isTokenExpired()) {
            return $this->getBackupPosts($feed);
        }

        $provider = $this->resolve($account->platform);

        $posts = $provider->fetchPosts($account, [
            'posts_count' => $feed->posts_count,
            'feed_type' => $feed->feed_type,
            ...(array) $feed->settings,
        ]);

        if ($posts->isEmpty()) {
            return $this->getBackupPosts($feed);
        }

        SocialPost::upsertFromApi($feed, $posts);

        return $posts;
    }

    private function getBackupPosts(SocialFeed $feed): Collection
    {
        return $feed->posts()
            ->orderByDesc('posted_at')
            ->limit($feed->posts_count)
            ->get()
            ->map(fn (SocialPost $post) => $post->only([
                'platform_post_id', 'post_type', 'content', 'media',
                'engagement', 'author_name', 'author_avatar_url',
                'permalink', 'posted_at', 'raw_data',
            ]));
    }

    private function applyFilters(SocialFeed $feed, Collection $posts): Collection
    {
        $filtered = $posts;

        // Skrýt nedostupné příspěvky (bez textu i bez médií)
        if ($feed->filterSetting('hide_unavailable')) {
            $filtered = $filtered->filter(function (mixed $post) {
                $postData = $this->normalizePost($post);

                if ($this->hasUnavailableAttachment($postData)) {
                    return false;
                }

                $hasContent = ! empty($postData['content']);
                $hasMedia = ! empty($postData['media']) && $postData['media'] !== '[]';

                return $hasContent || $hasMedia;
            });
        }

        // Minimální počet interakcí
        $minEngagement = (int) $feed->filterSetting('min_engagement', 0);
        if ($minEngagement > 0) {
            $filtered = $filtered->filter(function (mixed $post) use ($minEngagement) {
                $postData = $this->normalizePost($post);
                $engagement = is_array($postData['engagement'] ?? null) ? $postData['engagement'] : [];
                $total = ($engagement['reactions'] ?? $engagement['likes'] ?? 0)
                    + ($engagement['comments'] ?? 0)
                    + ($engagement['shares'] ?? 0);

                return $total >= $minEngagement;
            });
        }

        // Vyloučit typy příspěvků
        $excludeTypes = $feed->filterSetting('exclude_types', []);
        if (! empty($excludeTypes)) {
            $filtered = $filtered->reject(function (mixed $post) use ($excludeTypes): bool {
                $postData = $this->normalizePost($post);

                return in_array($postData['post_type'] ?? '', $excludeTypes, true);
            });
        }

        return $filtered->values();
    }

    private function normalizePost(mixed $post): array
    {
        if (is_array($post)) {
            return $post;
        }

        if (is_object($post)) {
            return (array) $post;
        }

        return [];
    }

    private function hasUnavailableAttachment(array $postData): bool
    {
        $rawData = $postData['raw_data'] ?? [];

        if (! is_array($rawData)) {
            return false;
        }

        $attachments = data_get($rawData, 'attachments.data', []);

        if (! is_array($attachments) || empty($attachments)) {
            return false;
        }

        foreach ($attachments as $attachment) {
            $attachmentData = is_array($attachment) ? $attachment : (array) $attachment;
            $title = (string) ($attachmentData['title'] ?? '');
            $description = (string) ($attachmentData['description'] ?? '');
            $normalizedText = str("{$title} {$description}")->lower()->squish()->toString();

            if ($normalizedText === '') {
                continue;
            }

            if ($this->isUnavailableAttachmentText($normalizedText)) {
                return true;
            }
        }

        return false;
    }

    private function isUnavailableAttachmentText(string $text): bool
    {
        $unavailablePhrases = [
            'obsah teď není dostupný',
            'obsah ted neni dostupny',
            'tento obsah není dostupný',
            'tento obsah neni dostupny',
            "content isn't available",
            'content is not available',
            "this content isn't available",
            'this content is not available',
            'content unavailable',
        ];

        foreach ($unavailablePhrases as $phrase) {
            if (str_contains($text, $phrase)) {
                return true;
            }
        }

        return false;
    }
}
