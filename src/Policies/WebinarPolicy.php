<?php

namespace EscolaLms\Webinar\Policies;

use EscolaLms\Auth\Models\User;
use EscolaLms\Webinar\Enum\WebinarPermissionsEnum;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebinarPolicy
{
    use HandlesAuthorization;

    public function list(User $user): bool
    {
        return $user->canAny([WebinarPermissionsEnum::WEBINAR_LIST, WebinarPermissionsEnum::WEBINAR_LIST_OWN]);
    }

    public function create(User $user): bool
    {
        return $user->can(WebinarPermissionsEnum::WEBINAR_CREATE);
    }

    public function delete(User $user, Webinar $webinar): bool
    {
        return $user->can(WebinarPermissionsEnum::WEBINAR_DELETE)
            || (
                $user->can(WebinarPermissionsEnum::WEBINAR_DELETE_OWN)
                && $webinar->trainers()->where('trainer_id', $user->getKey())->exists()
            );
    }

    public function update(User $user, Webinar $webinar): bool
    {
        return $user->can(WebinarPermissionsEnum::WEBINAR_UPDATE)
            || (
                $user->can(WebinarPermissionsEnum::WEBINAR_UPDATE_OWN)
                && $webinar->trainers()->where('trainer_id', $user->getKey())->exists()
            );
    }

    public function read(User $user, Webinar $webinar): bool
    {
        return $user->can(WebinarPermissionsEnum::WEBINAR_READ)
            || (
                $user->can(WebinarPermissionsEnum::WEBINAR_READ_OWN)
                && $webinar->trainers()->where('trainer_id', $user->getKey())->exists()
            );
    }
}
