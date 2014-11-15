<?php

namespace GVinaccia\Pusher\Facades;

use Illuminate\Support\Facades\Facade as Base;

class Pusher extends Base
{
    protected static function getFacadeAccessor()
    {
        return 'pusher.message.notifier';
    }
}