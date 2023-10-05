<?php

namespace EscolaLms\Webinar\Enum;

use EscolaLms\Core\Enums\BasicEnum;

class WebinarPermissionsEnum extends BasicEnum
{
    const WEBINAR_LIST = 'webinar_list';
    const WEBINAR_CREATE = 'webinar_create';
    const WEBINAR_UPDATE = 'webinar_update';
    const WEBINAR_DELETE = 'webinar_delete';
    const WEBINAR_READ = 'webinar_read';

    const WEBINAR_LIST_OWN = 'webinar_list-own';
    const WEBINAR_UPDATE_OWN = 'webinar_update-own';
    const WEBINAR_DELETE_OWN = 'webinar_delete-own';
    const WEBINAR_READ_OWN = 'webinar_read-own';
}
