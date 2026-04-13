<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Filament\Resources\SocialAccountResource\Pages;

use Filament\Resources\Pages\EditRecord;
use MiPress\SocialFeeds\Filament\Concerns\HasContextualCrudNotifications;
use MiPress\SocialFeeds\Filament\Resources\SocialAccountResource;

class EditSocialAccount extends EditRecord
{
    use HasContextualCrudNotifications;

    protected static string $resource = SocialAccountResource::class;
}
