<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\DestroyUnitRequest;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Http\Resources\Api\V1\UnitResource;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UnitController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return UnitResource::collection(Unit::query()->orderBy('name')->paginate(25));
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        $unit = Unit::create($request->validated());

        return UnitResource::make($unit)->response()->setStatusCode(201);
    }

    public function show(Unit $unit): UnitResource
    {
        return UnitResource::make($unit);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): UnitResource
    {
        $unit->update($request->validated());

        return UnitResource::make($unit);
    }

    public function destroy(DestroyUnitRequest $request, Unit $unit): Response
    {
        $unit->delete();

        return response()->noContent();
    }
}
