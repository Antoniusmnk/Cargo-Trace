<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * Get the tracking events timeline for the shipment.
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'asc');
    }

    /**
     * Get the latest tracking event for the shipment.
     */
    public function latestTrackingEvent(): HasOne
    {
        return $this->hasOne(TrackingEvent::class)->latestOfMany('event_time');
    }

    /**
     * Get all recorded disruption exceptions for the shipment.
     */
    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class)->latest('detected_at');
    }

    /**
     * Get active unresolved exceptions (OPEN or INVESTIGATING).
     */
    public function openExceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class)->whereIn('status', ['OPEN', 'INVESTIGATING']);
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

    /**
     * Get Tailwind CSS badge color classes based on delay severity.
     */
    public function getDelayBadgeColorAttribute(): string
    {
        return match ($this->delay_severity) {
            'NONE' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            'LOW' => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
            'MEDIUM' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            'HIGH' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
            default => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
        };
    }

    /**
     * Get a human-readable delay summary status text.
     */
    public function getDelayStatusTextAttribute(): string
    {
        if (! $this->is_delayed || $this->delay_severity === 'NONE') {
            return 'On Time';
        }

        return "Delayed (+{$this->delay_hours} hrs) [{$this->delay_severity}]";
    }
}
