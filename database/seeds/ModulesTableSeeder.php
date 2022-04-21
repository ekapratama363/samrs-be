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
        /** =========== USER ====================== */
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

        /** =========== ROLE ====================== */
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


        /** =========== SETTING ====================== */
        Modules::updateOrCreate(
            ['object' => 'setting-view'],
            ['description' => 'Global Setting - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'setting-update'],
            ['description' => 'Global Setting - Update']
        );

        /** =========== CLASSIFICATION MATERIAL ====================== */
        Modules::updateOrCreate(
            ['object' => 'classification-view'],
            ['description' => 'Classification Master - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'classification-create'],
            ['description' => 'Classification Master - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'classification-update'],
            ['description' => 'Classification Master - Edit']
        );

        /** =========== PLANT ====================== */
        Modules::updateOrCreate(
            ['object' => 'plant-view'],
            ['description' => 'Plant - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'plant-create'],
            ['description' => 'Plant - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'plant-update'],
            ['description' => 'Plant - Edit']
        );

        /** =========== MATERIAL ====================== */
        Modules::updateOrCreate(
            ['object' => 'material-view'],
            ['description' => 'Material - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'material-create'],
            ['description' => 'Material - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'material-update'],
            ['description' => 'Material - Edit']
        );

        /** =========== UNIT ====================== */
        Modules::updateOrCreate(
            ['object' => 'unit-view'],
            ['description' => 'Unit - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'unit-create'],
            ['description' => 'Unit - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'unit-update'],
            ['description' => 'Unit - Edit']
        );

        /** =========== VENDOR ====================== */
        Modules::updateOrCreate(
            ['object' => 'vendor-view'],
            ['description' => 'Vendor - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'vendor-create'],
            ['description' => 'Vendor - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'vendor-update'],
            ['description' => 'Vendor - Edit']
        );

        /** =========== ROOM ====================== */
        Modules::updateOrCreate(
            ['object' => 'room-view'],
            ['description' => 'Room - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'room-create'],
            ['description' => 'Room - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'room-update'],
            ['description' => 'Room - Edit']
        );
        
        //Assign All Module To Admin Role
        $role_admin = Role::where(DB::raw("LOWER(name)"), 'admin')->first();
        $modules    = Modules::all()->pluck('id');
        $role_admin->modules()->sync($modules);
    }
}
