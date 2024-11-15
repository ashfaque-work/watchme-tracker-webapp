<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class HrRoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        $permission = Permission::create(['name' => 'edit_user_log']);

        $role = Role::create(['name' => 'hr']);
        $role->givePermissionTo($permission);

        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->givePermissionTo($permission);

        $superAdminRole = Role::where('name', 'super-admin')->first();
        $superAdminRole->givePermissionTo($permission);
        
        $hr = User::create([
            'name' => 'Hr',
            'email' => 'hr@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'shift_id' => 1
        ]);
        
        $hr->assignRole('hr');
    }
}
