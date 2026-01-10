<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $super = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $redac = Role::firstOrCreate(['name' => 'Redacteur']);
        $valid = Role::firstOrCreate(['name' => 'Validateur']);

        $email = env('SEED_ADMIN_EMAIL', 'admin@umg.local');
        $password = env('SEED_ADMIN_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Super Admin', 'password' => Hash::make($password)]
        );

        if (!$admin->hasRole('SuperAdmin')) {
            $admin->assignRole($super);
        }
    }
}
