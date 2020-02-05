<?php

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
<<<<<<< HEAD
        $this->call(UserTableSeeder::class);
        $this->call(RoleTableSeeder::class);
=======
        $this->call(RolesTableSeeder::class);
        $this->call(UserTableSeeder::class);
>>>>>>> master
        $this->call(ModulesTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
    }
}
