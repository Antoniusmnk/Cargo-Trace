<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carriers = [
            [
                'code' => 'MSK',
                'name' => 'Maersk Line',
                'contact_email' => 'support@maersk.com',
                'phone' => '+45 3363 3363',
                'status' => 'active',
            ],
            [
                'code' => 'MSC',
                'name' => 'Mediterranean Shipping Company',
                'contact_email' => 'info@msc.com',
                'phone' => '+41 22703 8888',
                'status' => 'active',
            ],
            [
                'code' => 'CMA',
                'name' => 'CMA CGM Group',
                'contact_email' => 'contact@cma-cgm.com',
                'phone' => '+33 4 88 91 90 00',
                'status' => 'active',
            ],
            [
                'code' => 'DHL',
                'name' => 'DHL Global Forwarding',
                'contact_email' => 'freight@dhl.com',
                'phone' => '+49 228 1820',
                'status' => 'active',
            ],
        ];

        foreach ($carriers as $carrier) {
            Carrier::updateOrCreate(
                ['code' => $carrier['code']],
                $carrier
            );
        }
    }
}
