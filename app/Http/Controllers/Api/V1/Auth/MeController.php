<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Auth\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $request->user()->load('roles.permissions');

        return response()->json([
            'user' => (new UserResource($request->user()))->resolve(),
        ]);
    }
}
