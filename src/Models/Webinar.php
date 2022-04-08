<?php

namespace EscolaLms\Webinar\Models;

use EscolaLms\Core\Models\User;
use EscolaLms\Tags\Models\Tag;
use EscolaLms\Webinar\Database\Factories\WebinarFactory;
use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
 *          property="short_desc",
 *          description="short_desc",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="agenda",
 *          description="agenda",
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
 *      @OA\Property(
 *          property="yt_url",
 *          description="yt_url",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="yt_stream_url",
 *          description="yt_stream_url",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="yt_stream_key",
 *          description="yt_stream_key",
 *          type="string",
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
        'agenda',
        'short_desc',
        'image_path',
        'duration',
        'active_from',
        'active_to',
        'reminder_status',
    ];

    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'webinar_trainers','webinar_id', 'trainer_id') ;
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, 'morphable');
    }

    public function isPublished(): bool
    {
        return $this->status === WebinarStatusEnum::PUBLISHED;
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->attributes['image_path'] ?? null) {
            return url(Storage::url($this->attributes['image_path']));
        }
        return '';
    }

    public function hasYT(): bool
    {
        return $this->yt_url && $this->yt_stream_url && $this->yt_stream_key;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'webinar_user');
    }

    public function getBuyablePrice(?array $options = null): int
    {
        return $this->base_price ?? 0;
    }

    protected static function newFactory(): WebinarFactory
    {
        return WebinarFactory::new();
    }
}
