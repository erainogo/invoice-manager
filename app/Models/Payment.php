<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_file_id',
        'customer_id',
        'customer_email',
        'customer_name',
        'reference_number',
        'payment_date',
        'original_amount',
        'original_currency',
        'usd_amount',
        'status',
        'error_message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'payment_file_id' => 'integer',
            'payment_date' => 'datetime',
            'original_amount' => 'decimal:2',
            'usd_amount' => 'decimal:2',
        ];
    }

    public function paymentFile(): BelongsTo
    {
        return $this->belongsTo(PaymentFile::class);
    }
}
