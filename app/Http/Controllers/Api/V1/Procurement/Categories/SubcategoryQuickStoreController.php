<?php

namespace App\Http\Controllers\Api\V1\Procurement\Categories;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Vendors\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubcategoryQuickStoreController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $categoryId = (int) $request->input('category_id');
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

        $nameEnExists = Subcategory::query()
            ->where('category_id', $categoryId)
            ->where('name_en', $nameEn)
            ->exists();

        $slugExists = Subcategory::query()
            ->where('category_id', $categoryId)
            ->where('slug', $slug)
            ->exists();

        $duplicateErrors = [];
        if ($nameEnExists) {
            $duplicateErrors['name_en'] = ['This English name already exists for this category.'];
        }
        if ($slugExists) {
            $duplicateErrors['name_en'] = $duplicateErrors['name_en'] ?? [];
            $duplicateErrors['name_en'][] = 'The generated slug already exists for this category. Please change the English name.';
        }

        if ($duplicateErrors !== []) {
            return response()->json([
                'errors' => $duplicateErrors,
            ], 422);
        }

        $subcategory = Subcategory::query()->create([
            'category_id' => $categoryId,
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'slug' => $slug,
            'status' => 'active',
        ]);

        return response()->json([
            'id' => $subcategory->id,
            'name_ar' => $subcategory->name_ar,
            'name_en' => $subcategory->name_en,
        ], 201);
    }
}
