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

        Modules::updateOrCreate(
            ['object' => 'role-delete'],
            ['description' => 'Role - Delete']
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

        Modules::updateOrCreate(
            ['object' => 'classification-delete'],
            ['description' => 'Classification Master - Delete']
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

        Modules::updateOrCreate(
            ['object' => 'plant-delete'],
            ['description' => 'Plant - Delete']
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

        Modules::updateOrCreate(
            ['object' => 'material-delete'],
            ['description' => 'Material - Delete']
        );

        /** =========== MATERIAL SOURCING ====================== */
        Modules::updateOrCreate(
            ['object' => 'material-sourcing-view'],
            ['description' => 'Material Sourcing - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'material-sourcing-create'],
            ['description' => 'Material Sourcing - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'material-sourcing-update'],
            ['description' => 'Material Sourcing - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'material-sourcing-delete'],
            ['description' => 'Material Sourcing - Delete']
        );

        /** =========== STOCK ====================== */
        Modules::updateOrCreate(
            ['object' => 'stock-view'],
            ['description' => 'Stock - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-create'],
            ['description' => 'Stock - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-update'],
            ['description' => 'Stock - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-delete'],
            ['description' => 'Stock - Delete']
        );

        /** =========== STOCK OPNAME ====================== */
        Modules::updateOrCreate(
            ['object' => 'stock-opname-view'],
            ['description' => 'Stock Opname - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-opname-create'],
            ['description' => 'Stock Opname - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-opname-update'],
            ['description' => 'Stock Opname - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-opname-delete'],
            ['description' => 'Stock Opname - Delete']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-opname-reject'],
            ['description' => 'Stock Opname - Reject']
        );

        /** =========== Unit of Measurement ====================== */
        Modules::updateOrCreate(
            ['object' => 'unit-of-measurement-view'],
            ['description' => 'Unit of Measurement - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'unit-of-measurement-create'],
            ['description' => 'Unit of Measurement - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'unit-of-measurement-update'],
            ['description' => 'Unit of Measurement - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'unit-of-measurement-delete'],
            ['description' => 'Unit of Measurement - Delete']
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

        Modules::updateOrCreate(
            ['object' => 'vendor-delete'],
            ['description' => 'Vendor - Delete']
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

        Modules::updateOrCreate(
            ['object' => 'room-delete'],
            ['description' => 'Room - Delete']
        );

        /** =========== RESERVATION ====================== */
        Modules::updateOrCreate(
            ['object' => 'reservation-view'],
            ['description' => 'Reservation - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'reservation-create'],
            ['description' => 'Reservation - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'reservation-update'],
            ['description' => 'Reservation - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'reservation-delete'],
            ['description' => 'Reservation - Delete']
        );
        
        //Assign All Module To Admin Role
        $role_admin = Role::where(DB::raw("LOWER(name)"), 'admin')->first();
        $modules    = Modules::all()->pluck('id');
        $role_admin->modules()->sync($modules);
    }
}
