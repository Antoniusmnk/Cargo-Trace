<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Get shipments originating from this location.
     */
    public function originShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_id');
    }

    /**
     * Get shipments destined for this location.
     */
    public function destinationShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_id');
    }
}
