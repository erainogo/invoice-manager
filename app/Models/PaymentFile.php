<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function Symfony\Component\Translation\t;

class PaymentFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_name',
        'path',
        'status',
        'uploaded_at',
        'processed_at',
        'user_id',
    ];

    public function tryGetStream()
    {
        if (!$this->path || !Storage::disk('s3')->exists($this->path)) {
            Log::warning("Missing file path or file not found for PaymentFile ID {$this->id}");

            return null;
        }

        return Storage::disk('s3')->readStream($this->path);
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'uploaded_at' => 'timestamp',
            'processed_at' => 'timestamp',
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
