<?php

namespace EscolaLms\Webinar\Http\Requests;

use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Foundation\Http\FormRequest;

class BaseWebinarRequest extends FormRequest
{
    public function getWebinar(): Webinar
    {
        return Webinar::findOrFail($this->route('webinar'));
    }

    public function rules(): array
    {
        return [];
    }
}
