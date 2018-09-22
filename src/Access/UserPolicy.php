<?php

namespace Flagrow\Impersonate\Access;

use Flarum\User\AbstractPolicy;
use Flarum\User\User;

class UserPolicy extends AbstractPolicy
{
    protected $model = User::class;

    public function flagrowCanImpersonate(User $actor, User $user)
    {
        return $actor->can('flagrow-impersonate.login') && $actor->id !== $user->id;
    }
}
