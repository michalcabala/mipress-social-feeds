<?php

namespace MiPress\SocialFeeds\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use MiPress\SocialFeeds\Enums\FeedLayout;

class SocialFeed extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'social_account_id',
        'feed_type',
        'layout',
        'posts_count',
        'cache_ttl',
        'settings',
        'filter_settings',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'layout' => FeedLayout::class,
            'settings' => 'array',
            'filter_settings' => 'array',
            'is_active' => 'boolean',
            'posts_count' => 'integer',
            'cache_ttl' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $feed) {
            if (empty($feed->slug)) {
                $feed->slug = Str::slug($feed->name);
            }
            if (empty($feed->cache_ttl)) {
                $feed->cache_ttl = config('social-feeds.cache.default_ttl', 3600);
            }
        });
    }

    public function cacheKey(): string
    {
        $prefix = config('social-feeds.cache.prefix', 'social-feed');

        return "{$prefix}:{$this->id}";
    }

    // ── Display Settings Helpers ──

    public function displaySetting(string $key, mixed $default = null): mixed
    {
        $defaults = [
            'show_author' => true,
            'show_posted_at' => true,
            'show_engagement' => true,
            'show_permalink' => true,
            'content_length' => 300,
            'per_page' => 5,
            'pagination_type' => 'none',
            'columns' => 3,
        ];

        $resolvedDefault = $default ?? ($defaults[$key] ?? null);
        $value = data_get($this->settings, $key, $resolvedDefault);

        if ($value === null || $value === '') {
            $value = $resolvedDefault;
        }

        return match ($key) {
            'show_author', 'show_posted_at', 'show_engagement', 'show_permalink' => (bool) $value,
            'content_length' => max(50, (int) $value),
            'per_page' => max(1, (int) $value),
            'columns' => min(6, max(1, (int) $value)),
            'pagination_type' => in_array($value, ['none', 'load_more'], true) ? $value : 'none',
            default => $value,
        };
    }

    public function filterSetting(string $key, mixed $default = null): mixed
    {
        $defaults = [
            'hide_unavailable' => true,
            'min_engagement' => 0,
            'exclude_types' => [],
        ];

        return data_get($this->filter_settings, $key, $default ?? ($defaults[$key] ?? null));
    }

    // ── Relationships ──

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ──

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
