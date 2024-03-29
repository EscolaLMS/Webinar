<?php

namespace EscolaLms\Webinar\Http\Requests;

use EscolaLms\Webinar\Enum\WebinarPermissionsEnum;
use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreWebinarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Webinar::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'status' => ['required', 'string', Rule::in(WebinarStatusEnum::getValues())],
            'description' => ['required', 'string', 'min:3'],
            'agenda' => ['nullable', 'string', 'min:3'],
            'short_desc' => ['nullable', 'string', 'min:3'],
            'duration' => ['nullable', 'string', 'max:80'],
            'active_from' => ['date'],
            'active_to' => ['date', 'after_or_equal:active_from'],
            'image' => ['nullable', 'file', 'image'],
            'trainers' => ['array'],
            'trainers.*' => ['integer', 'exists:users,id'],
        ];
    }
}
