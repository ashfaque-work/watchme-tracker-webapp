<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'shift_id' => 1
        ]);

        $admin->assignRole('admin');
        
        $staff = User::create([
            'name' => 'Staff',
            'email' => 'staff@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'shift_id' => 1
        ]);

        $staff->assignRole('staff');

        $staff = User::create([
            'name' => 'Manager',
            'email' => 'manager@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'shift_id' => 1
        ]);
        $staff->assignRole('manager');
        $staff = User::create([
            'name' => 'Manager 2',
            'email' => 'manager2@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'shift_id' => 2
        ]);
        $staff->assignRole('manager');
    }
}
