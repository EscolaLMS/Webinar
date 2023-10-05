<?php

namespace EscolaLms\Webinar\Database\Seeders;

use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Webinar\Enum\WebinarPermissionsEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Seeder;

class WebinarsPermissionSeeder extends Seeder
{
    public function run()
    {
        // create permissions
        $admin = Role::findOrCreate(UserRole::ADMIN, 'api');
        $tutor = Role::findOrCreate(UserRole::TUTOR, 'api');

        foreach (WebinarPermissionsEnum::getValues() as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        $admin->givePermissionTo([
            WebinarPermissionsEnum::WEBINAR_LIST,
            WebinarPermissionsEnum::WEBINAR_UPDATE,
            WebinarPermissionsEnum::WEBINAR_DELETE,
            WebinarPermissionsEnum::WEBINAR_CREATE,
            WebinarPermissionsEnum::WEBINAR_READ,
        ]);

        $tutor->givePermissionTo([
            WebinarPermissionsEnum::WEBINAR_LIST_OWN,
            WebinarPermissionsEnum::WEBINAR_UPDATE_OWN,
            WebinarPermissionsEnum::WEBINAR_DELETE_OWN,
            WebinarPermissionsEnum::WEBINAR_READ_OWN,
            WebinarPermissionsEnum::WEBINAR_CREATE,
        ]);
    }
}
