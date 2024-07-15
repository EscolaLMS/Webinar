<?php

namespace EscolaLms\Webinar\Models;

use EscolaLms\Auth\Models\User as AuthUser;
use EscolaLms\Webinar\Models\Traits\HasWebinars;
use EscolaLms\Webinar\Tests\Database\Factories\UserFactory;

class User extends AuthUser
{
    use HasWebinars;

    public static function newFactory()
    {
        // @phpstan-ignore-next-line
        return UserFactory::new();
    }
}
