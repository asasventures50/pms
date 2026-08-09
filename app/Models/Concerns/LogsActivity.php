<?php

namespace App\Models\Concerns;

use App\Observers\ActivityLogObserver;
use BackedEnum;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::observe(ActivityLogObserver::class);
    }

    public function activityLogKey(): string
    {
        if (property_exists(static::class, 'activityLogKey')) {
            return (string) static::$activityLogKey;
        }

        return str(class_basename(static::class))->snake()->toString();
    }

    public function activityLogDescription(string $event): string
    {
        $entity = $this->activityLogEntityName();
        $label = $this->activityLogLabel();

        return match ($event) {
            'create' => "Created {$entity} {$label}",
            'update' => "Updated {$entity} {$label}",
            'delete' => "Deleted {$entity} {$label}",
            'restore' => "Restored {$entity} {$label}",
            default => ucfirst($event)." {$entity} {$label}",
        };
    }

    public function activityLogEntityName(): string
    {
        return match ($this->activityLogKey()) {
            'pr' => 'P.R.',
            'po' => 'P.O.',
            'rfq' => 'RFQ',
            default => str_replace('_', ' ', $this->activityLogKey()),
        };
    }

    public function activityLogLabel(): string
    {
        foreach ([
            'po_number',
            'rfq_number',
            'quotation_number',
            'invoice_number',
            'document_number',
            'vendor_code',
            'request_number',
            'iso_code',
            'code',
            'name',
            'name_en',
            'title',
            'file_name',
            'item_name',
            'item',
            'description',
        ] as $attribute) {
            if (! empty($this->{$attribute})) {
                return (string) $this->{$attribute};
            }
        }

        return class_basename(static::class).' #'.$this->getKey();
    }

    /**
     * @return list<string>
     */
    public function activityLogExcludedAttributes(): array
    {
        return [
            'password',
            'remember_token',
            'signature',
            'procurement_signature',
            'finance_signature',
            'ceo_signature',
            'vendor_rep_signature',
            'vendor_company_stamp',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function activityLogSnapshot(?array $only = null): array
    {
        $attributes = $only ?? array_keys($this->getAttributes());
        $excluded = $this->activityLogExcludedAttributes();
        $snapshot = [];

        foreach ($attributes as $key) {
            if (in_array($key, $excluded, true)) {
                continue;
            }

            $snapshot[$key] = $this->normalizeActivityLogValue($this->getAttribute($key));
        }

        return $snapshot;
    }

    public function normalizeActivityLogValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item) => $this->normalizeActivityLogValue($item), $value);
        }

        return $value;
    }
}
