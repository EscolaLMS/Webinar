<?php

namespace EscolaLms\Webinar\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use Illuminate\Http\Resources\Json\JsonResource;

class WebinarSimpleResource extends JsonResource
{
    use ResourceExtandable;

    public function toArray($request)
    {
        $fields = [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'active_from' => $this->active_from,
            'active_to' => $this->active_to,
            'name' => $this->name,
            'base_price' => $this->base_price,
            'status' => $this->status,
            'description' => $this->description,
            'duration' => $this->duration,
            'authors' => $this->authors,
            'image_path' => $this->image_path,
            'image_url' => $this->image_url,
        ];
        return self::apply($fields, $this);
    }
}
