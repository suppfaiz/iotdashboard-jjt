<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Admin User
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin Dashboard',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Seed Regular User
        User::updateOrCreate(
            ['email' => 'user@user.com'],
            [
                'name' => 'Pemantau IoT',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        // Seed Operational Groups
        $groupNames = [
            'FAI (Finance and Accounting)',
            'HR&GA',
            'CR and Surety',
            'Operasional',
            'Penjaminan Risiko'
        ];

        // Rename group 1 to preserve existing devices
        $group1 = \App\Models\Group::find(1);
        if ($group1) {
            $group1->update(['name' => $groupNames[0]]);
        }

        foreach ($groupNames as $name) {
            \App\Models\Group::firstOrCreate(['name' => $name]);
        }
    }
}

