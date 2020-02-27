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




        /** =========== USER GROUP ====================== */
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




        /** =========== RELEASE GROUP ====================== */
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



        /** =========== RELEASE CODE ====================== */
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



        /** =========== RELEASE OBJECT ====================== */
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



        /** =========== CLASSIFICATION TYPE ====================== */
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


        /** =========== CLASSIFICATION MATERIAL ====================== */
        Modules::updateOrCreate(
            ['object' => 'classification-material-view'],
            ['description' => 'Classification Master - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'classification-material-create'],
            ['description' => 'Classification Master - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'classification-material-update'],
            ['description' => 'Classification Master - Edit']
        );



        /** =========== LOCATION TYPE ====================== */
        Modules::updateOrCreate(
            ['object' => 'location-type-view'],
            ['description' => 'Location Type - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'location-type-create'],
            ['description' => 'Location Type - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'location-type-update'],
            ['description' => 'Location Type - Edit']
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


        /** =========== LOCATION ====================== */
        Modules::updateOrCreate(
            ['object' => 'location-view'],
            ['description' => 'Location - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'location-create'],
            ['description' => 'Location - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'location-update'],
            ['description' => 'Location - Edit']
        );


        /** =========== STORAGE ====================== */
        Modules::updateOrCreate(
            ['object' => 'storage-view'],
            ['description' => 'Storage - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'storage-create'],
            ['description' => 'Storage - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'storage-update'],
            ['description' => 'Storage - Edit']
        );



        //Assign All Module To Admin Role
        $role_admin = Role::where(DB::raw("LOWER(name)"), 'admin')->first();
        $modules    = Modules::all()->pluck('id');
        $role_admin->modules()->sync($modules);
    }
}
