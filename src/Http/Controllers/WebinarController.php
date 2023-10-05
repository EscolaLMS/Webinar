<?php

namespace EscolaLms\Webinar\Http\Controllers;

use EscolaLms\Auth\Dtos\Admin\UserAssignableDto;
use EscolaLms\Auth\Http\Resources\UserFullResource;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Webinar\Http\Requests\DeleteWebinarRequest;
use EscolaLms\Webinar\Http\Requests\ShowWebinarRequest;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Webinar\Enum\WebinarPermissionsEnum;
use EscolaLms\Webinar\Http\Requests\StoreWebinarRequest;
use EscolaLms\Webinar\Http\Requests\UpdateWebinarRequest;
use EscolaLms\Webinar\Dto\WebinarDto;
use EscolaLms\Webinar\Enum\ConstantEnum;
use EscolaLms\Webinar\Http\Controllers\Swagger\WebinarSwagger;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Webinar\Http\Requests\ListWebinarsRequest;
use EscolaLms\Webinar\Http\Requests\WebinarAssignableUserListRequest;
use EscolaLms\Webinar\Http\Resources\WebinarSimpleResource;
use EscolaLms\Webinar\Services\Contracts\WebinarServiceContract;
use Illuminate\Http\JsonResponse;

class WebinarController extends EscolaLmsBaseController implements WebinarSwagger
{
    private WebinarServiceContract $webinarServiceContract;
    private UserServiceContract $userService;

    public function __construct(
        WebinarServiceContract $webinarServiceContract,
        UserServiceContract $userService
    ) {
        $this->webinarServiceContract = $webinarServiceContract;
        $this->userService = $userService;
    }

    public function index(ListWebinarsRequest $listWebinarsRequest): JsonResponse
    {
        $search = $listWebinarsRequest->except(['limit', 'skip', 'order', 'order_by']);
        $orderDto = OrderDto::instantiateFromRequest($listWebinarsRequest);
        $webinars = $this->webinarServiceContract
            ->getWebinarsList($search, false, $orderDto)
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

    public function show(int $id, ShowWebinarRequest $request): JsonResponse
    {
        $webinar = $this->webinarServiceContract->show($id);
        return $this->sendResponseForResource(
            $this->webinarServiceContract->extendResponse(WebinarSimpleResource::make($webinar)),
            __('Webinar updated successfully')
        );
    }

    public function destroy(int $id, DeleteWebinarRequest $request): JsonResponse
    {
        $this->webinarServiceContract->delete($id);
        return $this->sendSuccess(__('Webinar deleted successfully'));
    }

    public function assignableUsers(WebinarAssignableUserListRequest $request): JsonResponse
    {
        $dto = UserAssignableDto::instantiateFromArray(array_merge($request->validated(), ['assignable_by' => WebinarPermissionsEnum::WEBINAR_CREATE]));
        $result = $this->userService
            ->assignableUsersWithCriteria($dto, $request->get('per_page'), $request->get('page'));
        return $this->sendResponseForResource(UserFullResource::collection($result), __('Users assignable to courses'));
    }
}
