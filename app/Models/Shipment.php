<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'shipment_number',
        'customer_id',
        'carrier_id',
        'origin_id',
        'destination_id',
        'cargo_type',
        'total_weight_kg',
        'total_volume_cbm',
        'expected_departure',
        'expected_arrival',
        'estimated_arrival',
        'current_status',
        'is_delayed',
        'delay_hours',
        'delay_severity',
        'notes',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'current_status' => 'CREATED',
        'is_delayed' => false,
        'delay_hours' => 0,
        'delay_severity' => 'NONE',
        'total_weight_kg' => 0,
        'total_volume_cbm' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected_departure' => 'datetime',
            'expected_arrival' => 'datetime',
            'estimated_arrival' => 'datetime',
            'is_delayed' => 'boolean',
            'delay_hours' => 'integer',
            'total_weight_kg' => 'decimal:2',
            'total_volume_cbm' => 'decimal:2',
        ];
    }

    /**
     * Get the customer that owns the shipment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the carrier handling the shipment.
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    /**
     * Get the origin location of the shipment.
     */
    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    /**
     * Get the destination location of the shipment.
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    /**
     * Get the cargo items for the shipment.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    /**
     * Scope a query to only include shipments for a given customer.
     */
    public function scopeForCustomer(Builder $query, int|string $userId): Builder
    {
        return $query->where('customer_id', $userId);
    }

    /**
     * Scope a query to filter shipments by current status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('current_status', $status);
    }

    /**
     * Scope a query to only include delayed shipments.
     */
    public function scopeDelayed(Builder $query): Builder
    {
        return $query->where('is_delayed', true);
    }
}
