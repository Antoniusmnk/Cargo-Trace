<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentException extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shipment_exceptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'shipment_id',
        'reported_by',
        'exception_type',
        'severity',
        'status',
        'detected_at',
        'resolved_at',
        'impact_hours',
        'description',
        'resolution_notes',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'severity' => 'MEDIUM',
        'status' => 'OPEN',
        'impact_hours' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'impact_hours' => 'integer',
        ];
    }

    /**
     * Get the shipment affected by this disruption.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get the user who reported this disruption.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Scope a query to only include active/open exceptions.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['OPEN', 'INVESTIGATING']);
    }

    /**
     * Scope a query to only include resolved exceptions.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'RESOLVED');
    }
}
