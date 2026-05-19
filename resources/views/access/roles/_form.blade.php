@php($role = $role ?? null)
@php($isSystemRole = $isSystemRole ?? false)

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="label" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Display name <span class="text-red-600">*</span></label>
        <input type="text" id="label" name="label" required
               value="{{ old('label', $role?->label ?? '') }}"
               @if ($isSystemRole) readonly @endif
               class="admin-filter-control @error('label') border-red-500 @enderror @if ($isSystemRole) bg-slate-50 @endif">
        @error('label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Slug <span class="text-red-600">*</span></label>
        <input type="text" id="name" name="name" required
               value="{{ old('name', $role?->name ?? '') }}"
               @if ($isSystemRole) readonly @endif
               class="admin-filter-control font-mono text-sm @error('name') border-red-500 @enderror @if ($isSystemRole) bg-slate-50 @endif">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        @if ($isSystemRole)
            <p class="mt-1 text-xs text-slate-500">System role slug cannot be changed.</p>
        @endif
    </div>
</div>

<div class="mt-8">
    <h2 class="text-sm font-semibold text-slate-900">Permissions</h2>
    <p class="mt-1 text-sm text-slate-600">Select what this role is allowed to do.</p>
    <div class="mt-4">
        @include('access.roles._permissions', [
            'permissionGroups' => $permissionGroups,
            'permissionLabels' => $permissionLabels,
            'selectedPermissions' => $selectedPermissions,
        ])
    </div>
    @error('permissions')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    @error('permissions.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
        Save Role
    </button>
    <a href="{{ route('roles.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Cancel
    </a>
</div>
