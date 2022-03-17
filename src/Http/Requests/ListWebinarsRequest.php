<?php

namespace EscolaLms\Webinar\Http\Requests;

use EscolaLms\Webinar\Enum\WebinarPermissionsEnum;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ListWebinarsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['string'],
            'base_price' => ['integer'],
            'status' => ['array'],
            'status.*' => ['string'],
        ];
    }
}
