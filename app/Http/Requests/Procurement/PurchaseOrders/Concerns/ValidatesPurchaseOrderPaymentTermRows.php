<?php

namespace App\Http\Requests\Procurement\PurchaseOrders\Concerns;

use Illuminate\Validation\Validator;

trait ValidatesPurchaseOrderPaymentTermRows
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validatePaymentTermMilestones($validator);
        });
    }

    private function validatePaymentTermMilestones(Validator $validator): void
    {
        if (! $this->exists('payment_term_rows') || ! is_array($this->input('payment_term_rows'))) {
            return;
        }

        $seen = [];

        foreach ($this->input('payment_term_rows') as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $milestone = trim((string) ($row['milestone'] ?? ''));
            $notes = trim((string) ($row['notes'] ?? ''));
            $hasPercentage = ($row['percentage'] ?? null) !== null && $row['percentage'] !== '';
            $hasAmount = ($row['amount'] ?? null) !== null && $row['amount'] !== '';

            if ($milestone === '' && $notes === '' && ! $hasPercentage && ! $hasAmount) {
                continue;
            }

            $field = 'payment_term_rows.'.$index.'.milestone';

            if ($milestone === '') {
                $validator->errors()->add($field, 'Payment term is required.');

                continue;
            }

            $key = mb_strtolower($milestone);
            if (isset($seen[$key])) {
                $message = 'Payment term must be unique in this table.';
                $validator->errors()->add($field, $message);
                $validator->errors()->add('payment_term_rows.'.$seen[$key].'.milestone', $message);

                continue;
            }

            $seen[$key] = $index;
        }
    }
}
