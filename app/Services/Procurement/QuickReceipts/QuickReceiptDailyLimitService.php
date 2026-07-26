<?php

namespace App\Services\Procurement\QuickReceipts;

use App\Enums\Procurement\QuickReceipts\QuickReceiptStatus;
use App\Models\Procurement\QuickReceipts\QuickReceipt;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class QuickReceiptDailyLimitService
{
    /**
     * Sum of pending_approval + approved receipt amounts for the employee on a given expense date.
     */
    public function spentOnDate(User $user, CarbonInterface|string $date, ?int $excludeReceiptId = null): float
    {
        $day = Carbon::parse($date)->toDateString();

        $query = QuickReceipt::query()
            ->where('user_id', $user->id)
            ->whereDate('expense_date', $day)
            ->whereIn(
                'status',
                array_map(
                    static fn (QuickReceiptStatus $status) => $status->value,
                    QuickReceiptStatus::countsTowardDailyLimit()
                )
            );

        if ($excludeReceiptId !== null) {
            $query->where('id', '!=', $excludeReceiptId);
        }

        return round((float) $query->sum('amount'), 2);
    }

    public function remainingOnDate(User $user, CarbonInterface|string $date, ?int $excludeReceiptId = null): float
    {
        $limit = $user->dailyReceiptLimitAmount();
        $spent = $this->spentOnDate($user, $date, $excludeReceiptId);

        return round(max(0, $limit - $spent), 2);
    }

    /**
     * Ensure adding/updating a receipt amount would not exceed the employee's daily limit.
     *
     * @throws ValidationException
     */
    public function assertWithinLimit(
        User $user,
        float $amount,
        CarbonInterface|string $date,
        ?int $excludeReceiptId = null,
    ): void {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Receipt amount must be greater than zero.',
            ]);
        }

        $limit = $user->dailyReceiptLimitAmount();
        $spent = $this->spentOnDate($user, $date, $excludeReceiptId);
        $projected = round($spent + $amount, 2);

        if ($projected <= $limit) {
            return;
        }

        $remaining = round(max(0, $limit - $spent), 2);
        $day = Carbon::parse($date)->toDateString();

        throw ValidationException::withMessages([
            'amount' => sprintf(
                'Daily receipt limit exceeded for %s. Limit: %s, already used: %s, remaining: %s, this receipt: %s.',
                $day,
                number_format($limit, 2),
                number_format($spent, 2),
                number_format($remaining, 2),
                number_format($amount, 2),
            ),
        ]);
    }
}
