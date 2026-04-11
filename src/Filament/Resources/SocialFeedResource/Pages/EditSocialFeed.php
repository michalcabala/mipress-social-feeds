<?php

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
                ->label('Obnovit feed')
                ->icon('fal-arrows-rotate')
                ->action(function () {
                    RefreshFeedJob::dispatchSync($this->record->id);
                    Notification::make()
                        ->title('Feed byl obnoven')
                        ->body('Feed "'.$this->record->name.'" byl úspěšně synchronizován.')
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
