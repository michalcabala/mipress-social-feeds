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

    public ?Model $record = null;

    protected int|string|array $columnSpan = 'full';

    #[On('feed-updated')]
    public function refreshPreview(): void
    {
        $this->record?->refresh();
    }

    protected function getViewData(): array
    {
        if (! $this->record instanceof SocialFeed) {
            return ['feed' => null, 'posts' => collect()];
        }

        $manager = app(SocialFeedManager::class);
        $posts = $manager->getFeedData($this->record);

        return [
            'feed' => $this->record,
            'posts' => $posts,
        ];
    }
}
