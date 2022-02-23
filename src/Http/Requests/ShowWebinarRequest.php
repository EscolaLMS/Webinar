<?php

namespace EscolaLms\Consultations\Http\Requests;

use EscolaLms\Webinar\Enum\WebinarPermissionsEnum;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ShowWebinarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(WebinarPermissionsEnum::WEBINAR_READ, Webinar::class);
    }

    public function rules(): array
    {
        return [];
    }
}
