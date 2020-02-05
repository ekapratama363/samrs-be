<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_admin = Role::where('name', 'admin')->first();
	    $role_anggota  = Role::where('name', 'member')->first();

	    $admin = new User();
	    $admin->firstname = 'Admin';
	    $admin->lastname = 'Admin';
	    $admin->username = 'Admin';
	    $admin->status = 1;
	    $admin->email = 'admin@localhost.com';
	    $admin->password = bcrypt('admin');
	    $admin->api_token = str_random(100);
	    $admin->save();
	    $admin->roles()->attach($role_admin);

	    $anggota = new User();
	    $anggota->firstname = 'Member';
	    $anggota->lastname = 'Member';
	    $anggota->username = 'Member';
	    $anggota->status = 1;
	    $anggota->email = 'member@localhost.com';
	    $anggota->password = bcrypt('member');
	    $anggota->api_token = str_random(100);
	    $anggota->save();
		$anggota->roles()->attach($role_anggota);

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
