<?php

namespace EscolaLms\Webinar\Models;

use EscolaLms\Core\Models\User as CoreUser;
use EscolaLms\Webinar\Models\Traits\HasWebinars;
use EscolaLms\Webinar\Tests\Database\Factories\UserFactory;

class User extends Coreuser
{
    use HasWebinars;

    public static function newFactory()
    {
        return UserFactory::new();
    }
}
