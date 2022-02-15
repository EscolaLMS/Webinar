<?php

namespace EscolaLms\Webinar\Models;

use EscolaLms\Auth\Models\User;
use EscolaLms\Webinar\Database\Factories\WebinarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @OA\Schema(
 *      schema="Webinar",
 *      required={"name", "status", "description", "author_id"},
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
 *          property="author_id",
 *          description="author_id",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="base_price",
 *          description="base_price",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="finished_at",
 *          description="finished_at",
 *          type="datetime",
 *      ),
 *      @OA\Property(
 *          property="started_at",
 *          description="started_at",
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
        'author_id',
        'started_at',
        'finished_at'
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected static function newFactory(): WebinarFactory
    {
        return WebinarFactory::new();
    }
}
