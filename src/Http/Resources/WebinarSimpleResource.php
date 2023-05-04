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
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'active_from' => Carbon::make($this->active_from),
            'active_to' => Carbon::make($this->active_to),
            'name' => $this->name,
            'status' => $this->status,
            'description' => $this->description,
            'short_desc' => $this->short_desc,
            'agenda' => $this->agenda,
            'duration' => $this->getDuration(),
            'trainers' => TrainerResource::collection($this->trainers),
            'image_path' => $this->image_path,
            'image_url' => $this->image_url,
            'logotype_path' => $this->logotype_path,
            'logotype_url' => $this->logotype_url,
            'yt_url' => $this->yt_url,
            'tags' => $this->tags,
        ];
        return self::apply($fields, $this);
    }
}
