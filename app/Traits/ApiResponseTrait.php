<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponseTrait
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(mixed $data = null, string $message = 'messages.success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => __($message),
            'data'    => $data,
        ], $code);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $message = 'messages.error', int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => __($message),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a paginated JSON response.
     */
    protected function paginatedResponse(mixed $resource, string $message = 'messages.fetched'): JsonResponse
    {
        $paginated = $resource->resource;

        return response()->json([
            'status'  => true,
            'message' => __($message),
            'data'    => $resource->resolve(request()),
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }
}
