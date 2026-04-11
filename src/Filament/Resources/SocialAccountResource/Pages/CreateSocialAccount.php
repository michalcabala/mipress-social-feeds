<?php

namespace MiPress\SocialFeeds\Filament\Resources\SocialAccountResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use MiPress\SocialFeeds\Filament\Concerns\HasContextualCrudNotifications;
use MiPress\SocialFeeds\Filament\Resources\SocialAccountResource;

class CreateSocialAccount extends CreateRecord
{
    use HasContextualCrudNotifications;

    protected static string $resource = SocialAccountResource::class;
}
