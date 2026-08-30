<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Activity\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class LogoutController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->activityLogger->logLogout($user ? (int) $user->id : null, $request);

        $token = $user?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }
}
