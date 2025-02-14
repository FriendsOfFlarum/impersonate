<?php

namespace FoF\Impersonate\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;

class UserResourceFields
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }
    
    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('canFoFImpersonate')
                ->visible(fn (User $user, Context $context) => $context->getActor()->can('fofCanImpersonate', $user))
                ->get(function (User $user, Context $context) {
                    $actor = $context->getActor();

                    return $actor->can('fofCanImpersonate', $user);
                }),
            Schema\Boolean::make('impersonateReasonRequired')
            ->visible(fn (User $user, Context $context) => $context->getActor()->can('fofCanImpersonate', $user))
                ->get(function (User $user, Context $context) {
                    return $this->settings->get('fof-impersonate.require_reason');
                }),
        ];
    }
}
