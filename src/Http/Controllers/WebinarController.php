<?php

namespace EscolaLms\Webinar\Http\Controllers;

use EscolaLms\Webinar\Http\Requests\StoreWebinarRequest;
use EscolaLms\Webinar\Http\Requests\UpdateWebinarRequest;
use EscolaLms\Webinar\Dto\WebinarDto;
use EscolaLms\Webinar\Enum\ConstantEnum;
use EscolaLms\Webinar\Http\Controllers\Swagger\WebinarSwagger;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Webinar\Http\Requests\ListWebinarsRequest;
use EscolaLms\Webinar\Http\Resources\WebinarSimpleResource;
use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use Illuminate\Http\JsonResponse;

class WebinarController extends EscolaLmsBaseController implements WebinarSwagger
{
    private WebinarServiceContract $webinarServiceContract;

    public function __construct(
        WebinarServiceContract $webinarServiceContract
    ) {
        $this->webinarServiceContract = $webinarServiceContract;
    }

    public function index(ListWebinarsRequest $listWebinarsRequest): JsonResponse
    {
        $search = $listWebinarsRequest->except(['limit', 'skip', 'order', 'order_by']);
        $webinars = $this->webinarServiceContract
            ->getWebinarsList($search)
            ->paginate(
                $listWebinarsRequest->get('per_page') ??
                config('escolalms_webinar.perPage', ConstantEnum::PER_PAGE)
            );

        return $this->sendResponseForResource(
            $this->webinarServiceContract->extendResponse(WebinarSimpleResource::collection($webinars)),
            __('Webinars retrieved successfully')
        );
    }

    public function store(StoreWebinarRequest $storeWebinarRequest): JsonResponse
    {
        $dto = new WebinarDto($storeWebinarRequest->all());
        $webinar = $this->webinarServiceContract->store($dto);
        return $this->sendResponseForResource(
            $this->webinarServiceContract->extendResponse(WebinarSimpleResource::make($webinar)),
            __('Webinar saved successfully')
        );
    }

    public function update(int $id, UpdateWebinarRequest $updateWebinarRequest): JsonResponse
    {
        $dto = new WebinarDto($updateWebinarRequest->all());
        $webinar = $this->webinarServiceContract->update($id, $dto);
        return $this->sendResponseForResource(
            $this->webinarServiceContract->extendResponse(WebinarSimpleResource::make($webinar)),
            __('Webinar updated successfully')
        );
    }

    public function show(int $id): JsonResponse
    {
        $webinar = $this->webinarServiceContract->show($id);
        return $this->sendResponseForResource(
            $this->webinarServiceContract->extendResponse(WebinarSimpleResource::make($webinar)),
            __('Webinar updated successfully')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->webinarServiceContract->delete($id);
        return $this->sendSuccess(__('Webinar deleted successfully'));
    }
}
