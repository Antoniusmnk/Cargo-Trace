<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShipmentException>
 */
class ShipmentExceptionFactory extends Factory
{
    protected $model = ShipmentException::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipment_id' => Shipment::factory(),
            'reported_by' => User::factory(),
            'exception_type' => fake()->randomElement([
                'CUSTOMS_HOLD',
                'WEATHER_DELAY',
                'PORT_CONGESTION',
                'DAMAGED',
                'MISROUTED',
                'DOCUMENTATION_ISSUE',
            ]),
            'severity' => fake()->randomElement(['LOW', 'MEDIUM', 'HIGH']),
            'status' => 'OPEN',
            'detected_at' => now(),
            'resolved_at' => null,
            'impact_hours' => fake()->numberBetween(2, 24),
            'description' => fake()->sentence(),
            'resolution_notes' => null,
        ];
    }
}
