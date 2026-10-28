<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@carpool.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('GantiPassword123!'),
            ]
        );
    }
}
