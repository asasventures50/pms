@php
    use App\Support\Access\UserDepartment;
    $user = $user ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Name <span class="text-red-600">*</span></label>
        <input type="text" id="name" name="name" required
               value="{{ old('name', $user?->name ?? '') }}"
               class="admin-filter-control @error('name') border-red-500 @enderror">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Email <span class="text-red-600">*</span></label>
        <input type="email" id="email" name="email" required
               value="{{ old('email', $user?->email ?? '') }}"
               class="admin-filter-control @error('email') border-red-500 @enderror">
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="department" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Department <span class="text-red-600">*</span></label>
        <select id="department" name="department" required
                class="admin-filter-control @error('department') border-red-500 @enderror">
            @foreach (UserDepartment::options() as $value => $label)
                <option value="{{ $value }}" @selected(old('department', $user?->department ?? UserDepartment::DEFAULT) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('department')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="currency_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Default currency (ISO 4217)</label>
        <input type="text" id="currency_code" name="currency_code" maxlength="3"
               value="{{ old('currency_code', $user?->defaultCurrencyCode() ?? '') }}"
               class="admin-filter-control uppercase @error('currency_code') border-red-500 @enderror"
               placeholder="USD">
        @error('currency_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-slate-500">Used on purchase orders when no vendor currency is set.</p>
    </div>

    <div>
        <label for="password" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
            Password @if (! $user?->exists)<span class="text-red-600">*</span>@else<span class="normal-case text-slate-400">(leave blank to keep)</span>@endif
        </label>
        <input type="password" id="password" name="password" @if (! $user?->exists) required @endif
               class="admin-filter-control @error('password') border-red-500 @enderror">
        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="admin-filter-control">
    </div>
</div>

<div class="mt-6">
    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Roles</p>
    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($roles as $role)
            <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                       @checked(in_array($role->name, $selectedRoles ?? [], true))>
                <span>{{ $role->label }}</span>
            </label>
        @endforeach
    </div>
    @error('roles')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    @error('roles.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
        Save User
    </button>
    <a href="{{ route('users.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Cancel
    </a>
</div>
