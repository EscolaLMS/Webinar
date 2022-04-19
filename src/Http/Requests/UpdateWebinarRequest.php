<?php

namespace EscolaLms\Webinar\Http\Requests;

use EscolaLms\Webinar\Enum\WebinarPermissionsEnum;
use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateWebinarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(WebinarPermissionsEnum::WEBINAR_UPDATE, Webinar::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255', 'min:3'],
            'status' => ['string', Rule::in(WebinarStatusEnum::getValues())],
            'description' => ['string', 'min:3'],
            'duration' => ['nullable', 'string', 'max:80'],
            'agenda' => ['nullable', 'string', 'min:3'],
            'short_desc' => ['nullable', 'string', 'min:3'],
            'active_from' => ['date'],
            'active_to' => ['date', 'after_or_equal:active_from'],
            'image' => ['nullable', 'file', 'image'],
            'trainers' => ['array'],
            'trainers.*' => ['integer', 'exists:users,id'],
        ];
    }
}
