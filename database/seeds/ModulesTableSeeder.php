<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Modules;
use App\Models\Role;

class ModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // User
        Modules::updateOrCreate(
            ['object' => 'user-view'],
            ['description' => 'User List - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'user-create'],
            ['description' => 'User List - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'user-update'],
            ['description' => 'User Login Detail - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'user-update-profile'],
            ['description' => 'User Profile - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'user-update-profile-self'],
            ['description' => 'User Profile Self - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'user-view-login-history'],
            ['description' => 'User Login History - View']
        );

        // User group
        Modules::updateOrCreate(
            ['object' => 'user-group-view'],
            ['description' => 'User group List - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'user-group-create'],
            ['description' => 'User group List - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'user-group-update'],
            ['description' => 'User group Login Detail - Edit']
        );

        // Role
        Modules::updateOrCreate(
            ['object' => 'role-view'],
            ['description' => 'Role - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'role-create'],
            ['description' => 'Role - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'role-update'],
            ['description' => 'Role - Edit']
        );

        // Classification Type
        Modules::updateOrCreate(
            ['object' => 'classification-type-view'],
            ['description' => 'Classification Type - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'classification-type-create'],
            ['description' => 'Classification Type - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'classification-type-update'],
            ['description' => 'Classification Type - Edit']
        );


        // Setting
        Modules::updateOrCreate(
            ['object' => 'setting-view'],
            ['description' => 'Global Setting - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'setting-update'],
            ['description' => 'Global Setting - Update']
        );


        // Release Group
        Modules::updateOrCreate(
            ['object' => 'release-group-view'],
            ['description' => 'Release Group - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'release-group-create'],
            ['description' => 'Release Group - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'release-group-update'],
            ['description' => 'Release Group - Edit']
        );

        // Release Code
        Modules::updateOrCreate(
            ['object' => 'release-code-view'],
            ['description' => 'Release Code - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'release-code-create'],
            ['description' => 'Release Code - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'release-code-update'],
            ['description' => 'Release Code - Edit']
        );

        // Release object
        Modules::updateOrCreate(
            ['object' => 'release-object-view'],
            ['description' => 'Release object - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'release-object-create'],
            ['description' => 'Release object - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'release-object-update'],
            ['description' => 'Release object - Edit']
        );

        // Release strategy
        Modules::updateOrCreate(
            ['object' => 'release-strategy-view'],
            ['description' => 'Release strategy - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'release-strategy-create'],
            ['description' => 'Release strategy - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'release-strategy-update'],
            ['description' => 'Release strategy - Edit']
        );

        //Assign All Module To Admin Role
        $role_admin = Role::where(DB::raw("LOWER(name)"), 'admin')->first();
        $modules    = Modules::all()->pluck('id');
        $role_admin->modules()->sync($modules);
    }
}
