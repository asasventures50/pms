<div class="space-y-6">
    @foreach ($permissionGroups as $group => $permissionNames)
        <fieldset class="rounded-lg border border-slate-200 p-4">
            <legend class="px-1 text-sm font-semibold text-slate-900">{{ $group }}</legend>
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach ($permissionNames as $permissionName)
                    <label class="flex items-start gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="permissions[]" value="{{ $permissionName }}"
                               class="mt-0.5"
                               @checked(in_array($permissionName, $selectedPermissions ?? [], true))>
                        <span>{{ $permissionLabels[$permissionName] ?? $permissionName }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach
</div>
