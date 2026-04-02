<?php

use EscolaLms\Webinar\Broadcasting\WebinarChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('webinar.{webinar}.{term}', WebinarChannel::class, ['middleware' => 'auth:api']);
