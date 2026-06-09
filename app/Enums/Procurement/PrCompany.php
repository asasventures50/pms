<?php

namespace App\Enums\Procurement;

/**
 * Buyer companies selectable on Procurement Requests.
 * Update company details and logos under public/images/pr/companies/{key}.png
 */
enum PrCompany: string
{
    case AsasVentures = 'asas_ventures';
    case QassiounJourney = 'qassioun_journey';
    case Activation = 'activation';

    public function label(): string
    {
        return match ($this) {
            self::AsasVentures => 'ASAS Ventures',
            self::QassiounJourney => 'Qassioun Journey',
            self::Activation => 'Activation',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    public static function resolve(?string $key): self
    {
        if ($key !== null && $key !== '') {
            return self::tryFrom($key) ?? self::AsasVentures;
        }

        return self::AsasVentures;
    }

    /**
     * @return array{name: string, address: string|null, phone: string|null, email: string|null, fax: string|null}
     */
    public function details(): array
    {
        return match ($this) {
            self::AsasVentures => [
                'name' => 'ASAS Ventures',
                'address' => 'Nouri Pasha, Arawda square, Damascus, Syria',
                'phone' => '011-3344955/ 011-3344954',
                'email' => 'asasventures.sy@gmail.com',
                'fax' => '011-3344953',
            ],
            self::QassiounJourney => [
                'name' => 'Qassioun Journey',
                'address' => null,
                'phone' => null,
                'email' => null,
                'fax' => null,
            ],
            self::Activation => [
                'name' => 'Activation',
                'address' => null,
                'phone' => null,
                'email' => null,
                'fax' => null,
            ],
        };
    }

    /**
     * @return array{name: string, address: string|null, phone: string|null, email: string|null, fax: string|null}
     */
    public static function forDisplay(?string $key): array
    {
        return self::resolve($key)->details();
    }

    public function logoRelativePath(): string
    {
        return 'images/pr/companies/'.$this->value.'.png';
    }

    public function logoExists(): bool
    {
        if (is_file(public_path($this->logoRelativePath()))) {
            return true;
        }

        return $this === self::AsasVentures && is_file(public_path('images/po/logo.png'));
    }

    public function logoUrl(): string
    {
        if (is_file(public_path($this->logoRelativePath()))) {
            return asset($this->logoRelativePath());
        }

        if ($this === self::AsasVentures && is_file(public_path('images/po/logo.png'))) {
            return asset('images/po/logo.png');
        }

        return asset($this->logoRelativePath());
    }

    public function logoFallbackHtml(): string
    {
        $parts = preg_split('/\s+/', $this->label()) ?: [$this->label()];

        return implode('<br>', array_map('strtoupper', $parts));
    }
}
