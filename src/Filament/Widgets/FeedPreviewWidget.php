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

        if (! $feed) {
            return ['feed' => null, 'posts' => collect()];
        }

        $manager = app(SocialFeedManager::class);
        $posts = $manager->getFeedData($feed);

        return [
            'feed' => $feed,
            'posts' => $posts,
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

        return null;
    }
}
