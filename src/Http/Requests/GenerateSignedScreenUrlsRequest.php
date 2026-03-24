<?php

namespace EscolaLms\Webinar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSignedScreenUrlsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'webinar_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
            'executed_at' => ['required'],
            'files' => ['array', 'min:1'],
            'files.*.filename' => ['required', 'string'],
        ];
    }
}
