<?php

namespace EscolaLms\Webinar\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TrainerInterestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'name_with_breadcrumbs' => $this->resource->name_with_breadcrumbs,
            'slug' => $this->resource->slug,
            'icon' => $this->resource->icon ? Storage::url($this->resource->icon) : null,
            'icon_class' => $this->resource->icon_class,
            'is_active' => $this->resource->is_active,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'parent_id' => $this->resource->parent_id,
        ];
    }
}
