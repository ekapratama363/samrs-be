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
        $this->call(RoleTableSeeder::class);
        $this->call(UsersTableSeeder::class);
>>>>>>> ruben_dev
        $this->call(ModulesTableSeeder::class);
    }
}
