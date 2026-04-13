<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Filament\Resources\SocialFeedResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use MiPress\SocialFeeds\Filament\Concerns\HasContextualCrudNotifications;
use MiPress\SocialFeeds\Filament\Resources\SocialFeedResource;

class CreateSocialFeed extends CreateRecord
{
    use HasContextualCrudNotifications;

    protected static string $resource = SocialFeedResource::class;
}
