<?php

namespace EscolaLms\Webinar\Models;

use EscolaLms\Auth\Models\User;
use EscolaLms\Webinar\Database\Factories\WebinarFactory;
use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *      schema="Webinar",
 *      required={"name", "status", "description"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          description="status",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="description",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="duration",
 *          description="duration",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="base_price",
 *          description="base_price",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="active_to",
 *          description="active_to",
 *          type="datetime",
 *      ),
 *      @OA\Property(
 *          property="active_from",
 *          description="active_from",
 *          type="datetime"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="datetime",
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="datetime",
 *      ),
 * )
 *
 */
class Webinar extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_price',
        'name',
        'status',
        'description',
        'image_path',
        'duration',
        'active_from',
        'active_to'
    ];

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'webinar_authors','webinar_id', 'author_id') ;
    }

    public function isPublished(): bool
    {
        return $this->status === WebinarStatusEnum::PUBLISHED;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (isset($this->attributes['image_path'])) {
            return url(Storage::disk('public')->url($this->attributes['image_path']));
        }
        return null;
    }

    protected static function newFactory(): WebinarFactory
    {
        return WebinarFactory::new();
    }
}
