<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\DestroyCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::query()->orderBy('name')->paginate(25));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Generate UUID dan catat data pembuatan
        $data['id'] = Str::uuid()->toString();
        $data['created_dt'] = now();
        $data['created_by'] = 'system';
        
        $category = Category::create($data);

        return CategoryResource::make($category)->response()->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $data = $request->validated();
        
        // Catat data perubahan
        $data['updated_dt'] = now();
        $data['updated_by'] = 'system';
        
        $category->update($data);

        return CategoryResource::make($category);
    }

    public function destroy(DestroyCategoryRequest $request, Category $category): Response
    {
        $category->delete();

        return response()->noContent();
    }
}