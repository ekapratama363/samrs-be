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
        $role_admin = Role::where('name', 'Super_Admin')->first();
	    $role_anggota  = Role::where('name', 'Member')->first();

	    $admin  = User::where('email', 'admin@localhost.com')->first();

		if(!$admin) {
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
		}

	    $admin1  = User::where('email', 'admin1@localhost.com')->first();

		if(!$admin1) {
			$admin1 = new User();
			$admin1->firstname = 'Admin1';
			$admin1->lastname = 'Admin1';
			$admin1->username = 'Admin1';
			$admin1->status = 1;
			$admin1->email = 'admin1@localhost.com';
			$admin1->password = bcrypt('admin');
			$admin1->api_token = str_random(100);
			$admin1->save();
			$admin1->roles()->attach($role_admin);		
		}

	    $member  = User::where('email', 'member@localhost.com')->first();
		if(!$member) {
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
		}
    }
}
