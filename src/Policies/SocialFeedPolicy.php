<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Policies;

use App\Models\User;
use MiPress\SocialFeeds\Models\SocialFeed;

class SocialFeedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('social_feed.view');
    }

    public function view(User $user, SocialFeed $socialFeed): bool
    {
        return $user->hasPermissionTo('social_feed.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('social_feed.create');
    }

    public function update(User $user, SocialFeed $socialFeed): bool
    {
        return $user->hasPermissionTo('social_feed.update');
    }

    public function delete(User $user, SocialFeed $socialFeed): bool
    {
        return $user->hasPermissionTo('social_feed.delete');
    }

    public function restore(User $user, SocialFeed $socialFeed): bool
    {
        return $user->hasPermissionTo('social_feed.delete');
    }

    public function forceDelete(User $user, SocialFeed $socialFeed): bool
    {
        return $user->hasPermissionTo('social_feed.delete');
    }
}
