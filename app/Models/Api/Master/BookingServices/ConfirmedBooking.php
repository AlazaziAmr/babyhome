<?php

namespace App\Models\Api\Master\BookingServices;

use App\Models\Api\Nurseries\Nursery;
use App\Models\Api\Master\Booking\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ConfirmedBooking extends Model
{
    use HasFactory,SoftDeletes;
    use HasTranslations;

    protected $fillable = [
        'nursery_id',
        'booking_id',
        'payment_method_id',
        'confirm_date',
        'total_payment',
        'price_per_hour',
        'total_services_price',
        'created_by',
        'status',
    ];


    public function nurseries(): HasMany
    {
        return $this->HasMany(Nursery::class, 'nursery_id', 'id');
    }
    public function Booking(): BelongsTo
    {
        return $this->BelongsTo(Booking::class, 'booking_id', 'id');
    }
    public function PaymentMethod(): BelongsTo
    {
        return $this->BelongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }
}
