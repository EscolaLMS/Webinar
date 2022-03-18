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
    public function generateJitsi(int $webinarId): array;
    public function setYtStream(Webinar $webinar): void;
    public function updateYTStream(Webinar $webinar): void;
    public function getWebinarsListForCurrentUser(array $search = []): Builder;
}
