<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
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
            ['name' => 'member']
        );

        Role::firstOrCreate(
            ['name' => 'technician']
        );
    }
}
