<?php

namespace FoF\Impersonate;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;

class AddUserImpersonateAttributes
{
    protected $settings;
    
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }
    
    public function __invoke(UserSerializer $serializer, User $user)
    {
        $attributes = [];
        $fofCanImpersonate = $serializer->getActor()->can('fofCanImpersonate', $user);

        if ($fofCanImpersonate) {
            $attributes['canFoFImpersonate'] = $fofCanImpersonate;
            $attributes['impersonateReasonRequired'] = (bool) $this->settings->get('fof-impersonate.require_reason');
        }

        return $attributes;
    }
}
