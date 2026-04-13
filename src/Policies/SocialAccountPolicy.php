<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Policies;

use App\Models\User;
use MiPress\SocialFeeds\Models\SocialAccount;

class SocialAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('social_account.view');
    }

    public function view(User $user, SocialAccount $socialAccount): bool
    {
        return $user->hasPermissionTo('social_account.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('social_account.create');
    }

    public function update(User $user, SocialAccount $socialAccount): bool
    {
        return $user->hasPermissionTo('social_account.update');
    }

    public function delete(User $user, SocialAccount $socialAccount): bool
    {
        return $user->hasPermissionTo('social_account.delete');
    }

    public function restore(User $user, SocialAccount $socialAccount): bool
    {
        return $user->hasPermissionTo('social_account.delete');
    }

    public function forceDelete(User $user, SocialAccount $socialAccount): bool
    {
        return $user->hasPermissionTo('social_account.delete');
    }
}
