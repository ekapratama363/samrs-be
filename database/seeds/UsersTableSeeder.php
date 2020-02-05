<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $admin = new User();
	    $admin->firstname = 'Admin';
	    $admin->lastname = 'Admin';
	    $admin->username = 'Admin';
	    $admin->email = 'admin@localhost.com';
        $admin->password = bcrypt('123456');
	    $admin->status = 1;
	    $admin->api_token = str_random(100);
	    $admin->save();
        $admin->roles()->attach(Role::where('name', 'admin')->first());


        $technician = new User();
	    $technician->firstname = 'Technical';
	    $technician->lastname = 'Operation';
	    $technician->username = 'technician';
	    $technician->email = 'technician@localhost.com';
        $technician->password = bcrypt('123456');
	    $technician->status = 1;
	    $technician->api_token = str_random(100);
	    $technician->save();
	    $technician->roles()->attach(Role::where('name', 'technician')->first());

    }
}
