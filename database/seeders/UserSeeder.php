<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@cargotrace.com',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Logistics Operations Officer',
                'email' => 'operator@cargotrace.com',
                'role' => 'operator',
                'status' => 'active',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Global Importer Client',
                'email' => 'customer@cargotrace.com',
                'role' => 'customer',
                'status' => 'active',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
