<?php

namespace EscolaLms\Webinar\Http\Requests;

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
            'status' => ['array'],
            'status.*' => ['string'],
            'order_by' => ['string', 'in:id,name,status,duration,active_from,active_to,created_at,updated_at'],
            'order' => ['string', 'in:ASC,DESC'],
            'only_incoming' => ['boolean'],
        ];
    }
}
