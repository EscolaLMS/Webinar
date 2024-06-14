<?php

namespace EscolaLms\Webinar\Http\Resources;

use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerResource extends JsonResource
{
    public function toArray($request)
    {
        $fields = [
            'id' => $this->resource->id,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'email' => $this->resource->email,
            'path_avatar' => $this->resource->path_avatar,
            'url_avatar' => $this->resource->avatar_url,
            'interests' => TrainerInterestResource::collection($this->resource->interests),
        ];

        return array_merge(
            $fields,
            ['categories' => $this->resource->categories],
            ModelFields::getExtraAttributesValues($this->resource, MetaFieldVisibilityEnum::PUBLIC)
        );
    }
}
