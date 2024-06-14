<?php

namespace EscolaLms\Webinar\Http\Resources;

use Carbon\Carbon;
use EscolaLms\Auth\Traits\ResourceExtandable;
use Illuminate\Http\Resources\Json\JsonResource;

class WebinarSimpleResource extends JsonResource
{
    use ResourceExtandable;

    public function toArray($request)
    {
        $fields = [
            'id' => $this->resource->id,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'active_from' => Carbon::make($this->resource->active_from),
            'active_to' => Carbon::make($this->resource->active_to),
            'name' => $this->resource->name,
            'status' => $this->resource->status,
            'description' => $this->resource->description,
            'short_desc' => $this->resource->short_desc,
            'agenda' => $this->resource->agenda,
            'duration' => $this->resource->getDuration(),
            'trainers' => TrainerResource::collection($this->resource->trainers),
            'image_path' => $this->resource->image_path,
            'image_url' => $this->resource->image_url,
            'logotype_path' => $this->resource->logotype_path,
            'logotype_url' => $this->resource->logotype_url,
            'yt_url' => $this->resource->yt_url,
            'tags' => $this->resource->tags,
            'deadline' => $this->resource->deadline,
        ];
        return self::apply($fields, $this);
    }
}
