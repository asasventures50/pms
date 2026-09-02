<?php

namespace App\Http\Controllers\Api\V1\Access\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreUserRequest;
use App\Http\Requests\Access\UpdateUserRequest;
use App\Http\Resources\Api\V1\Access\Users\UserResource;
use App\Models\Access\Role;
use App\Models\User;
use App\Support\Access\PermissionCatalog;
use App\Support\Access\UserDepartment;
use App\Support\TableSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $sort = TableSort::resolve($request, ['name', 'email', 'department', 'created_at'], 'name', 'asc');

        $query = User::query()->with('roles');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('department', 'like', $term);
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->string('role')));
        }

        if ($request->filled('department')) {
            $query->where('department', $request->string('department'));
        }

        $query->orderBy($sort['column'], $sort['direction'])->orderBy('id');

        return UserResource::collection($query->paginate($perPage)->withQueryString())->additional(
            $this->formOptions()
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'department' => $data['department'],
            'currency_code' => self::normalizeCurrencyCode($data['currency_code'] ?? null),
            'daily_receipt_limit' => self::normalizeDailyReceiptLimit($data['daily_receipt_limit'] ?? null),
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles($data['roles'] ?? []);
        $user->load('roles');

        return (new UserResource($user))
            ->additional(array_merge($this->formOptions(), ['message' => 'User created successfully.']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        $user->load('roles');

        return (new UserResource($user))->additional($this->formOptions());
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'department' => $data['department'],
            'currency_code' => self::normalizeCurrencyCode($data['currency_code'] ?? null),
            'daily_receipt_limit' => self::normalizeDailyReceiptLimit($data['daily_receipt_limit'] ?? null),
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles($data['roles'] ?? []);
        $user->load('roles');

        return (new UserResource($user))
            ->additional(array_merge($this->formOptions(), ['message' => 'User updated successfully.']))
            ->response();
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->isSuperAdmin() && User::query()->whereHas('roles', fn ($q) => $q->where('name', PermissionCatalog::SUPER_ADMIN_ROLE))->count() <= 1) {
            return response()->json([
                'message' => 'Cannot delete the only super admin account.',
            ], 422);
        }

        if ($user->id === $request->user()?->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user->roles()->detach();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
/******* */
    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $departments = [];
        foreach (UserDepartment::options() as $value => $label) {
            $departments[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return [
            'roles' => Role::query()->orderBy('label')->get(['name', 'label'])->map(fn (Role $role) => [
                'name' => $role->name,
                'label' => $role->label,
            ])->values(),
            'departments' => $departments,
        ];
    }

    private static function normalizeCurrencyCode(mixed $raw): ?string
    {
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        return strtoupper(trim((string) $raw));
    }

    private static function normalizeDailyReceiptLimit(mixed $raw): float
    {
        if ($raw === null || $raw === '') {
            return 200.0;
        }

        return round((float) $raw, 2);
    }
}
