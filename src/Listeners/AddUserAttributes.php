<?php

namespace Flagrow\Impersonate\Listeners;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Event\PrepareApiAttributes;
use Illuminate\Contracts\Events\Dispatcher;

class AddUserAttributes
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(PrepareApiAttributes::class, [$this, 'addAttributes']);
    }

    public function addAttributes(PrepareApiAttributes $event)
    {
        if ($event->isSerializer(UserSerializer::class)) {
            $event->attributes['flagrowCanImpersonate'] = $event->actor->can('flagrowCanImpersonate', $event->model);
        }
    }
}
