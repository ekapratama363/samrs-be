<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
<<<<<<< HEAD:database/seeds/RoleTableSeeder.php
class RoleTableSeeder extends Seeder
=======
class RolesTableSeeder extends Seeder
>>>>>>> master:database/seeds/RolesTableSeeder.php
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::firstOrCreate(
            ['name' => 'admin']
        );

        Role::firstOrCreate(
            ['name' => 'member']
<<<<<<< HEAD:database/seeds/RoleTableSeeder.php
        );

        Role::firstOrCreate(
            ['name' => 'technician']
=======
>>>>>>> master:database/seeds/RolesTableSeeder.php
        );
    }
}
