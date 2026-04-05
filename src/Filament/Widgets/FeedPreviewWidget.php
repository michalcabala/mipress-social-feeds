<?php

namespace MiPress\SocialFeeds\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use MiPress\SocialFeeds\Models\SocialFeed;
use MiPress\SocialFeeds\Services\SocialFeedManager;

class FeedPreviewWidget extends Widget
{
    protected string $view = 'social-feeds::filament.widgets.feed-preview';

    public SocialFeed|Model|int|string|null $record = null;

    public ?array $debugInfo = null;

    protected int|string|array $columnSpan = 'full';

    #[On('feed-updated')]
    public function refreshPreview(): void
    {
        $feed = $this->resolveFeed();

        if (! $feed) {
            return;
        }

        $feed->refresh();
        $this->record = $feed;
    }

    protected function getViewData(): array
    {
        $feed = $this->resolveFeed();

        $this->debugInfo = [
            'record_type' => is_object($this->record) ? $this->record::class : gettype($this->record),
            'route_record_type' => is_object(request()->route('record')) ? request()->route('record')::class : gettype(request()->route('record')),
            'resolved_feed_id' => $feed?->getKey(),
        ];

        if (! $feed) {
            return [
                'feed' => null,
                'posts' => collect(),
                'debugInfo' => $this->debugInfo,
            ];
        }

        $manager = app(SocialFeedManager::class);
        $posts = $manager->getFeedData($feed);

        $this->debugInfo['posts_count'] = $posts->count();

        return [
            'feed' => $feed,
            'posts' => $posts,
            'debugInfo' => $this->debugInfo,
        ];
    }

    private function resolveFeed(): ?SocialFeed
    {
        if ($this->record instanceof SocialFeed) {
            return $this->record;
        }

        if ($this->record instanceof Model && $this->record->getKey()) {
            return SocialFeed::query()->find($this->record->getKey());
        }

        if (is_numeric($this->record)) {
            return SocialFeed::query()->find((int) $this->record);
        }

        $routeRecord = request()->route('record');

        if ($routeRecord instanceof SocialFeed) {
            return $routeRecord;
        }

        if ($routeRecord instanceof Model && $routeRecord->getKey()) {
            return SocialFeed::query()->find($routeRecord->getKey());
        }

        if (is_numeric($routeRecord)) {
            return SocialFeed::query()->find((int) $routeRecord);
        }

        return null;
    }
}
