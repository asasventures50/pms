<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Models\User;
use App\Services\Activity\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function store(LoginRequest $request): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        $user = User::query()->where('email', $request->string('email')->toString())->first();

        if ($user === null || ! Hash::check($request->string('password')->toString(), $user->password)) {
            $request->hitRateLimiter();

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->clearRateLimiter();

        $user->load('roles.permissions');

        $token = $user->createToken('api-v1')->plainTextToken;

        $this->activityLogger->logLogin((int) $user->id, $request);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user))->resolve(),
        ]);
    }
}
