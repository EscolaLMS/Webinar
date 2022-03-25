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
            'short_desc' => $this->short_desc,
            'agenda' => $this->agenda,
            'duration' => $this->duration,
            'trainers' => $this->trainers,
            'image_path' => $this->image_path,
            'image_url' => $this->image_url,
            'yt_url' => $this->yt_url,
            'tags' => $this->tags,
        ];
        return self::apply($fields, $this);
    }
}
