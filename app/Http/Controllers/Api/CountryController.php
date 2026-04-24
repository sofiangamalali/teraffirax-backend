<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CountryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Services\CountryService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CountryService $countryService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Country::class);
        $countries = $this->countryService->getPaginatedCountries($request->query('per_page', 15));
        return $this->paginatedResponse(CountryResource::collection($countries), 'messages.fetched');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryRequest $request): JsonResponse
    {
        $this->authorize('create', Country::class);
        $dto = CountryDTO::fromRequest($request);
        $country = $this->countryService->createCountry($dto);
        return $this->successResponse(new CountryResource($country), 'messages.created', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Country $country): JsonResponse
    {
        $this->authorize('view', $country);
        return $this->successResponse(new CountryResource($country), 'messages.fetched');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryRequest $request, Country $country): JsonResponse
    {
        $this->authorize('update', $country);
        $dto = CountryDTO::fromRequest($request);
        $updatedCountry = $this->countryService->updateCountry($country->id, $dto);
        return $this->successResponse(new CountryResource($updatedCountry), 'messages.updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Country $country): JsonResponse
    {
        $this->authorize('delete', $country);
        $this->countryService->deleteCountry($country->id);
        return $this->successResponse(null, 'messages.deleted');
    }
}
