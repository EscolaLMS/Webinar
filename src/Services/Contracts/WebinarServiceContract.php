<?php

namespace EscolaLms\Webinar\Services\Contracts;

use EscolaLms\Webinar\Dto\WebinarDto;
use EscolaLms\Webinar\Models\Webinar;
use Illuminate\Database\Eloquent\Builder;

interface WebinarServiceContract
{
    public function getWebinarsList(array $search = [], bool $onlyActive = false): Builder;
    public function store(WebinarDto $webinarDto): Webinar;
    public function update(int $id, WebinarDto $webinarDto): Webinar;
    public function show(int $id): Webinar;
    public function delete(int $id): ?bool;
    public function setRelations(Webinar $webinar, array $relations = []): void;
    public function setFiles(Webinar $webinar, array $files = []): void;

    /**
     * @OA\Schema(
     *      schema="Jitsi",
     *      @OA\Property(
     *         property="data",
     *         type="array",
     *         example="Jitsi data object with iframe api doc",
     *      ),
     *      @OA\Property(
     *         property="domain",
     *         type="string",
     *         example="meet-stage.escolalms.com",
     *      ),
     *      @OA\Property(
     *         property="url",
     *         type="string",
     *         example="https://meet-stage.escolalms.com/asdhuasd.?jwt=token",
     *      ),
     *      @OA\Property(
     *         property="yt_url",
     *         type="string",
     *         example="https://youtube.pl/",
     *      ),
     *      @OA\Property(
     *         property="yt_stream_url",
     *         type="string",
     *         example="rtmp://a.rtmp.youtube.com/xyz",
     *      ),
     *      @OA\Property(
     *         property="yt_stream_key",
     *         type="string",
     *         example="asdqdfas123asdqwe",
     *      ),
     * )
     *
     */
    public function generateJitsi(int $webinarId): array;
    public function setYtStream(Webinar $webinar): void;
    public function updateYTStream(Webinar $webinar): void;
    public function getWebinarsListForCurrentUser(array $search = []): Builder;
}
