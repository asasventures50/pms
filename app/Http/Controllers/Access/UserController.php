<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreUserRequest;
use App\Http\Requests\Access\UpdateUserRequest;
use App\Models\Access\Role;
use App\Models\User;
use App\Support\Access\PermissionCatalog;
use App\Support\Access\UserDepartment;
use App\Support\TableSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
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

        return view('access.users.index', [
            'users' => $query->paginate($perPage)->appends($request->query()),
            'roles' => Role::query()->orderBy('label')->get(),
            'departments' => UserDepartment::options(),
            'sortColumn' => $sort['column'],
            'sortDirection' => $sort['direction'],
        ]);
    }

    public function create(): View
    {
        return view('access.users.create', [
            'user' => new User,
            'roles' => Role::query()->orderBy('label')->get(),
            'selectedRoles' => old('roles', []),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
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

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $user->load('roles');

        return view('access.users.edit', [
            'user' => $user,
            'roles' => Role::query()->orderBy('label')->get(),
            'selectedRoles' => old('roles', $user->roles->pluck('name')->all()),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
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

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin() && User::query()->whereHas('roles', fn ($q) => $q->where('name', PermissionCatalog::SUPER_ADMIN_ROLE))->count() <= 1) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Cannot delete the only super admin account.');
        }

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
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
