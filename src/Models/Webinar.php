<?php

namespace EscolaLms\Webinar\Models;

use EscolaLms\Tags\Models\Tag;
use EscolaLms\Webinar\Database\Factories\WebinarFactory;
use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
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
 *          property="image_path",
 *          description="image_path",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="image_url",
 *          description="image_url",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="logotype_path",
 *          description="logotype_path",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="logotype_url",
 *          description="logotype_url",
 *          type="string",
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
 * @property string $status
 * @property string $logotype_path
 * @property string $logotype_url
 * @property string $name
 * @property string $yt_url
 * @property string $yt_stream_url
 * @property string $yt_stream_key
 * @property ?string $yt_id
 * @property string $description
 * @property ?Carbon $active_to
 * @property bool $yt_autostart_status
 */
class Webinar extends Model
{
    use HasFactory;

    protected $fillable = [
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
            $path = trim(trim($this->attributes['image_path'], '/'));
            if ($path) {
                $imagePath = Storage::url($path);
                return preg_match('/^(http|https):.*$/', $imagePath, $oa) ?
                    $imagePath :
                    url($imagePath);
            }
        }
        return '';
    }

    public function getLogotypeUrlAttribute(): string
    {
        if ($this->attributes['logotype_path'] ?? null) {
            $path = trim(trim($this->attributes['logotype_path'], '/'));
            if ($path) {
                $logotype = Storage::url(trim($this->attributes['logotype_path'], '/'));
                return preg_match('/^(http|https):.*$/', $logotype, $oa) ?
                    $logotype :
                    url($logotype);
            }
        }
        return '';
    }

    public function hasYT(): bool
    {
        $webinarServiceContract = app(WebinarServiceContract::class);
        return $webinarServiceContract->hasYT($this);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'webinar_user');
    }

    public function getDuration(): string
    {
        return $this->duration ?? '';
    }

    protected static function newFactory(): WebinarFactory
    {
        return WebinarFactory::new();
    }

    public function getDeadlineAttribute()
    {
        $webinarServiceContract = app(WebinarServiceContract::class);
        return $webinarServiceContract->getWebinarEndDate($this);
    }
}
