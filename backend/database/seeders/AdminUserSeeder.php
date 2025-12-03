<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'pereztarirastalin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin'), // change in production! niet vergeten
            ]
        );

        // Set admin flags outside mass assignment
        $user->is_admin = true;
        $user->email_verified_at = now();
        $user->save();
    }
}
