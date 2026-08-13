<?php

namespace App\Models;

use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'code',
    'tax_id',
    'contact_name',
    'phone',
    'email',
    'postal_code',
    'city_id',
    'district_id',
    'address',
    'notes',
    'remittance_bank',
    'remittance_account',
    'settlement_method',
    'is_active',
])]
class Vendor extends Model
{
    /** @use HasFactory<VendorFactory> */
    use HasFactory;

    public const SETTLEMENT_CASH = 'cash';

    public const SETTLEMENT_MONTHLY_30 = 'monthly_30';

    public const SETTLEMENT_MONTHLY_60 = 'monthly_60';

    public const SETTLEMENT_MONTHLY_90 = 'monthly_90';

    public const SETTLEMENT_CHECK = 'check';

    public const SETTLEMENT_WIRE = 'wire';

    /**
     * @return array<string, string>
     */
    public static function settlementMethods(): array
    {
        return [
            self::SETTLEMENT_CASH => '現金',
            self::SETTLEMENT_MONTHLY_30 => '月結 30 天',
            self::SETTLEMENT_MONTHLY_60 => '月結 60 天',
            self::SETTLEMENT_MONTHLY_90 => '月結 90 天',
            self::SETTLEMENT_CHECK => '支票',
            self::SETTLEMENT_WIRE => '電匯',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @return BelongsTo<District, $this>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
