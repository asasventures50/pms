<?php

namespace App\Http\Controllers\Api\V1\Procurement\Categories;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Vendors\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryQuickStoreController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $nameAr = trim((string) $request->input('name_ar'));
        $nameEn = trim((string) $request->input('name_en'));
        $slug = Str::slug($nameEn);

        if ($slug === '') {
            return response()->json([
                'errors' => [
                    'name_en' => ['Unable to generate a slug from the English name.'],
                ],
            ], 422);
        }

        $slugExists = Category::query()
            ->where('slug', $slug)
            ->exists();

        if ($slugExists) {
            return response()->json([
                'errors' => [
                    'name_en' => ['The generated slug already exists. Please change the English name.'],
                ],
            ], 422);
        }

        $category = Category::query()->create([
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'slug' => $slug,
            'status' => 'active',
        ]);

        return response()->json([
            'id' => $category->id,
            'name_ar' => $category->name_ar,
            'name_en' => $category->name_en,
            'slug' => $category->slug,
        ], 201);
    }
}
