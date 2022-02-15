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

        Permission::findOrCreate(WebinarPermissionsEnum::WEBINAR_LIST, 'api');
        Permission::findOrCreate(WebinarPermissionsEnum::WEBINAR_UPDATE, 'api');
        Permission::findOrCreate(WebinarPermissionsEnum::WEBINAR_DELETE, 'api');
        Permission::findOrCreate(WebinarPermissionsEnum::WEBINAR_CREATE, 'api');
        Permission::findOrCreate(WebinarPermissionsEnum::WEBINAR_READ, 'api');

        $admin->givePermissionTo([
            WebinarPermissionsEnum::WEBINAR_LIST,
            WebinarPermissionsEnum::WEBINAR_UPDATE,
            WebinarPermissionsEnum::WEBINAR_DELETE,
            WebinarPermissionsEnum::WEBINAR_CREATE,
            WebinarPermissionsEnum::WEBINAR_READ,
        ]);
        $tutor->givePermissionTo([
            WebinarPermissionsEnum::WEBINAR_LIST,
            WebinarPermissionsEnum::WEBINAR_UPDATE,
            WebinarPermissionsEnum::WEBINAR_DELETE,
            WebinarPermissionsEnum::WEBINAR_CREATE,
            WebinarPermissionsEnum::WEBINAR_READ,
        ]);
    }
}
