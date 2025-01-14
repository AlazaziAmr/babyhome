<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        $this->call([
            DaysSeeder::class,
            PackageTypesSeeder::class,
            AdminSeeder::class,
            CountriesSeeder::class,
            CitiesSeeder::class,
            GeneralSeeder::class,
            RoleAndPermissionSeeder::class,
            InspectorRoleAndPermissionSeeder::class,
            AdminRoleAndPermissionSeeder::class
        ]);
    }
}
