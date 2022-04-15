<?php

namespace EscolaLms\Webinar\Http\Controllers;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Webinar\Enum\ConstantEnum;
use EscolaLms\Webinar\Http\Controllers\Swagger\WebinarAPISwagger;
use EscolaLms\Webinar\Http\Requests\ListWebinarsRequest;
use EscolaLms\Webinar\Http\Resources\WebinarSimpleResource;
use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use Illuminate\Http\JsonResponse;

class WebinarAPIController extends EscolaLmsBaseController implements WebinarAPISwagger
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
            ->getWebinarsList($search, true)
            ->paginate(
                $listWebinarsRequest->get('per_page') ??
                config('escolalms_webinar.perPage', ConstantEnum::PER_PAGE)
            );
        return $this->sendResponseForResource(
            $this->webinarServiceContract->extendResponse(WebinarSimpleResource::collection($webinars), true),
            __('Webinars retrieved successfully'),
        );
    }

    public function show(int $id): JsonResponse
    {
        $webinar = $this->webinarServiceContract->show($id);
        return $this->sendResponseForResource(
            $this->webinarServiceContract->extendResponse(WebinarSimpleResource::make($webinar), true),
            __('Webinar show successfully')
        );
    }

    public function forCurrentUser(ListWebinarsRequest $listWebinarsRequest): JsonResponse
    {
        $search = $listWebinarsRequest->except(['limit', 'skip', 'order', 'order_by']);
        $webinars = $this->webinarServiceContract
            ->getWebinarsListForCurrentUser($search)
            ->paginate(
                $listWebinarsRequest->get('per_page') ??
                config('escolalms_webinar.perPage', ConstantEnum::PER_PAGE)
            );

        return $this->sendResponseForResource(
            $this->webinarServiceContract->extendResponse(WebinarSimpleResource::collection($webinars), true),
             __('Webinars retrieved successfully')
        );
    }

    public function generateJitsi(int $id): JsonResponse
    {
        return $this->sendResponse(
            $this->webinarServiceContract->generateJitsi($id),
            __('Webinar jitsi url generated successfully')
        );
    }

    public function startLiveStream(int $id): void
    {
        /**
         * @param string second param "testing" | "live" | "complete"
         */
        $this->webinarServiceContract->setStatusInLiveStreamInYt($id, 'live');
    }

    public function stopLiveStream(int $id): void
    {
        /**
         * @param string second param "testing" | "live" | "complete"
         */
        $this->webinarServiceContract->setStatusInLiveStreamInYt($id, 'complete');
    }
}
