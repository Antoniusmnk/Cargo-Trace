<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'code' => 'IDTPP',
                'name' => 'Port of Tanjung Priok',
                'type' => 'port',
                'address' => 'Jl. Raya Pelabuhan No. 9, Tanjung Priok',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'latitude' => -6.1018800,
                'longitude' => 106.8833800,
            ],
            [
                'code' => 'IDSUB',
                'name' => 'Port of Tanjung Perak',
                'type' => 'port',
                'address' => 'Jl. Perak Timur No. 610, Pabean Cantikan',
                'city' => 'Surabaya',
                'country' => 'Indonesia',
                'latitude' => -7.1983000,
                'longitude' => 112.7336000,
            ],
            [
                'code' => 'SGSIN',
                'name' => 'Port of Singapore',
                'type' => 'port',
                'address' => '7B Keppel Rd, Tanjong Pagar Terminal',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'latitude' => 1.2644000,
                'longitude' => 103.8400000,
            ],
            [
                'code' => 'CNSHG',
                'name' => 'Port of Shanghai',
                'type' => 'port',
                'address' => 'Yangshan Deep-Water Port, Hangzhou Bay',
                'city' => 'Shanghai',
                'country' => 'China',
                'latitude' => 31.2304000,
                'longitude' => 121.4737000,
            ],
            [
                'code' => 'NLRTM',
                'name' => 'Port of Rotterdam',
                'type' => 'port',
                'address' => 'Wilhelminakade 902, 3072 AP',
                'city' => 'Rotterdam',
                'country' => 'Netherlands',
                'latitude' => 51.9244000,
                'longitude' => 4.4777000,
            ],
            [
                'code' => 'USLAX',
                'name' => 'Port of Los Angeles',
                'type' => 'port',
                'address' => '425 S. Palos Verdes St, San Pedro',
                'city' => 'Los Angeles',
                'country' => 'United States',
                'latitude' => 33.7432000,
                'longitude' => -118.2673000,
            ],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ['code' => $location['code']],
                $location
            );
        }
    }
}
