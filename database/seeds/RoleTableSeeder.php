<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
<<<<<<< HEAD

=======
>>>>>>> ruben_dev
class RoleTableSeeder extends Seeder
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
<<<<<<< HEAD
            ['name' => 'member']
=======
            ['name' => 'technician']
>>>>>>> ruben_dev
        );
    }
}
