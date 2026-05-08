<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentTemplateRequest;
use App\Http\Requests\UpdateContentTemplateRequest;
use App\Http\Requests\GetTemplateVariablesRequest;
use App\Http\Resources\ContentTemplateResource;
use App\DTOs\ContentTemplateDTO;
use App\Models\ContentTemplate;
use App\Services\ContentTemplateService;
use App\Support\TemplateVariableRegistry;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorContentTemplateController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected ContentTemplateService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ContentTemplate::class);
        
        $perPage = $request->query('per_page', 15);
        $templates = $this->service->paginateForCreator($request->user()->id, $perPage);
        
        return $this->paginatedResponse(
            ContentTemplateResource::collection($templates), 
            'messages.fetched'
        );
    }

    public function store(StoreContentTemplateRequest $request): JsonResponse
    {
        $this->authorize('create', ContentTemplate::class);
        
        $dto = ContentTemplateDTO::fromRequest($request);
        $template = $this->service->create($dto);
        
        return $this->successResponse(
            new ContentTemplateResource($template), 
            'messages.created', 
            201
        );
    }

    public function show(ContentTemplate $content_template): JsonResponse
    {
        $this->authorize('view', $content_template);
        
        return $this->successResponse(
            new ContentTemplateResource($content_template), 
            'messages.fetched'
        );
    }

    public function update(UpdateContentTemplateRequest $request, ContentTemplate $content_template): JsonResponse
    {
        $this->authorize('update', $content_template);
        
        $dto = ContentTemplateDTO::fromRequest($request);
        $this->service->update($content_template, $dto);
        
        return $this->successResponse(
            new ContentTemplateResource($content_template), 
            'messages.updated'
        );
    }

    public function destroy(ContentTemplate $content_template): JsonResponse
    {
        $this->authorize('delete', $content_template);
        
        $this->service->delete($content_template);
        
        return $this->successResponse(null, 'messages.deleted');
    }

    /**
     * Get allowed variables for a platform.
     */
    public function variables(GetTemplateVariablesRequest $request, TemplateVariableRegistry $registry): JsonResponse
    {
        $platform = $request->validated()['platform'];
        $variables = $registry->get($platform);
        return $this->successResponse($variables, 'messages.fetched');
    }
}
