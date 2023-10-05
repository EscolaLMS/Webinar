<?php

namespace EscolaLms\Webinar\Http\Requests;

use Illuminate\Support\Facades\Gate;

class DeleteWebinarRequest extends BaseWebinarRequest
{
    public function authorize(): bool
    {
        return Gate::allows('delete', $this->getWebinar());
    }
}
