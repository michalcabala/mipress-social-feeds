<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Filament\Resources\SocialFeedResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use MiPress\SocialFeeds\Filament\Concerns\HasContextualCrudNotifications;
use MiPress\SocialFeeds\Filament\Resources\SocialFeedResource;
use MiPress\SocialFeeds\Filament\Widgets\FeedPreviewWidget;
use MiPress\SocialFeeds\Jobs\RefreshFeedJob;

class EditSocialFeed extends EditRecord
{
    use HasContextualCrudNotifications;

    protected static string $resource = SocialFeedResource::class;

    protected function afterSave(): void
    {
        $this->dispatch('feed-updated');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label(__('social-feeds::admin.resources.social_feed.actions.refresh_now.label'))
                ->icon('fal-arrows-rotate')
                ->action(function () {
                    RefreshFeedJob::dispatchSync($this->record->id);
                    Notification::make()
                        ->title(__('social-feeds::admin.resources.social_feed.actions.refresh_now.success_title'))
                        ->body(__('social-feeds::admin.resources.social_feed.actions.refresh_now.success_body', ['name' => $this->record->name]))
                        ->success()
                        ->send();

                    $this->redirect(static::$resource::getUrl('edit', ['record' => $this->record]));
                }),
            DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            FeedPreviewWidget::make([
                'record' => $this->getRecord(),
            ]),
        ];
    }
}
