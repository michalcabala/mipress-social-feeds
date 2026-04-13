<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Filament\Resources\SocialFeedResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use MiPress\SocialFeeds\Filament\Resources\SocialFeedResource;

class ListSocialFeeds extends ListRecords
{
    protected static string $resource = SocialFeedResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
