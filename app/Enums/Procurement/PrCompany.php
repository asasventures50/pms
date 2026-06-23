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
     * @return array{
     *     name: string,
     *     address: string|null,
     *     phone: string|null,
     *     email: string|null,
     *     fax: string|null,
     *     commercial_registry: string|null,
     *     company_legal_type: string|null
     * }
     */
    public function details(): array
    {
        return match ($this) {
            self::AsasVentures => [
                'name' => 'ASAS Ventures',
                'address' => 'Nouri Pasha, Arawda square, Damascus, Syria',
                'phone' => '011-3344954 / 011-3344955',
                'email' => 'asasventures.sy@gmail.com',
                'fax' => '011-3344953',
                'commercial_registry' => 'مسجلة في السجل التجاري تحت الرقم: ۲۰۹۹۹',
                'company_legal_type' => 'شركة محدودة المسؤولية ورأسمالها خمسون مليون ليرة سورية',
            ],
            self::QassiounJourney => [
                'name' => 'Qassioun Journey',
                'address' => 'Nouri Pasha, Arawda square, Damascus, Syria',
                'phone' => '011-3344954 / 011-3344955',
                'email' => 'asasventures.sy@gmail.com',
                'fax' => '011-3344953',
                'commercial_registry' => null,
                'company_legal_type' => null,
            ],
            self::Activation => [
                'name' => 'Activation',
                'address' => 'Nouri Pasha, Arawda square, Damascus, Syria',
                'phone' => '011-3344954 / 011-3344955',
                'email' => 'asasventures.sy@gmail.com',
                'fax' => '011-3344953',
                'commercial_registry' => null,
                'company_legal_type' => null,
            ],
        };
    }

    /**
     * @return array{
     *     name: string,
     *     address: string|null,
     *     phone: string|null,
     *     email: string|null,
     *     fax: string|null,
     *     commercial_registry: string|null,
     *     company_legal_type: string|null
     * }
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

    /**
     * @return array{
     *     company_key: string,
     *     company_name: string|null,
     *     company_phone: string|null,
     *     company_email: string|null,
     *     company_address: string|null,
     *     company_website: string|null
     * }
     */
    public function toPurchaseOrderHeader(): array
    {
        $details = $this->details();

        return [
            'company_key' => $this->value,
            'company_name' => $details['name'],
            'company_phone' => $details['phone'],
            'company_email' => $details['email'],
            'company_address' => $details['address'],
            'company_website' => $details['fax'],
        ];
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     logo_url: string,
     *     logo_exists: bool,
     *     logo_fallback_html: string,
     *     buyer: array{
     *         name: string,
     *         address: string|null,
     *         phone: string|null,
     *         email: string|null,
     *         fax: string|null,
     *         commercial_registry: string|null,
     *         company_legal_type: string|null
     *     }
     * }
     */
    public function toPurchaseOrderApiPayload(): array
    {
        return [
            'key' => $this->value,
            'label' => $this->label(),
            'logo_url' => $this->logoUrl(),
            'logo_exists' => $this->logoExists(),
            'logo_fallback_html' => $this->logoFallbackHtml(),
            'buyer' => $this->details(),
        ];
    }
}
