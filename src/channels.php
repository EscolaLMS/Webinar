<?php

Broadcast::channel('webinar.{webinar}.{term}', ConsultationChannel::class, ['middleware' => 'auth:api']);
