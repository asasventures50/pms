<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Models\User;
use App\Support\Access\UserDepartment;

class ProcurementRequestRequestorResolver
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public static function applyForCreate(array &$validated, User $user): void
    {
        $validated['requestor_name'] = $user->name;
        $validated['requested_at'] = now()->toDateString();
        $validated['requestor_department'] = UserDepartment::label(
            $user->department ?? UserDepartment::DEFAULT
        );
    }
}
