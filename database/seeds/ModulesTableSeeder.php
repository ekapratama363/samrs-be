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
        /** =========== MENU ====================== */
        Modules::updateOrCreate(
            ['object' => 'room-menu'],
            ['description' => 'Room - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'plant-menu'],
            ['description' => 'Plant - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'vendor-menu'],
            ['description' => 'Vendor - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'unit-of-measurement-menu'],
            ['description' => 'Unit of Measurment - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'classification-menu'],
            ['description' => 'Classification - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'material-menu'],
            ['description' => 'Material - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'material-sourcing-menu'],
            ['description' => 'Material Sourcing - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-menu'],
            ['description' => 'Stock - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'reservation-menu'],
            ['description' => 'Reservation - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'purchase-order-menu'],
            ['description' => 'Purchase Order - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'delivery-order-menu'],
            ['description' => 'Delivery Order - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'good-receives-menu'],
            ['description' => 'Good Receives - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-opname-menu'],
            ['description' => 'Stock Opname - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'user-menu'],
            ['description' => 'User - Menu']
        );

        Modules::updateOrCreate(
            ['object' => 'role-menu'],
            ['description' => 'Role - Menu']
        );

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

        // Modules::updateOrCreate(
        //     ['object' => 'stock-create'],
        //     ['description' => 'Stock - Create']
        // );

        // Modules::updateOrCreate(
        //     ['object' => 'stock-update'],
        //     ['description' => 'Stock - Edit']
        // );

        // Modules::updateOrCreate(
        //     ['object' => 'stock-delete'],
        //     ['description' => 'Stock - Delete']
        // );

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

        Modules::updateOrCreate(
            ['object' => 'stock-opname-approve'],
            ['description' => 'Stock Opname - Approve']
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
            ['object' => 'reservation-reject'],
            ['description' => 'Reservation - Reject']
        );

        Modules::updateOrCreate(
            ['object' => 'reservation-approve'],
            ['description' => 'Reservation - Approve']
        );

        /** =========== PURCHASE ORDER ====================== */
        Modules::updateOrCreate(
            ['object' => 'purchase-order-view'],
            ['description' => 'Purchase Order - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'purchase-order-process'],
            ['description' => 'Purchase Order - Process']
        );

        /** =========== DELIVERY ORDER ====================== */
        Modules::updateOrCreate(
            ['object' => 'delivery-order-view'],
            ['description' => 'Delivery Order - Display']
        );

        /** =========== GOOD RECEIVE ====================== */
        Modules::updateOrCreate(
            ['object' => 'good-receive-view'],
            ['description' => 'Good Receive - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'good-receive-reject'],
            ['description' => 'Good Receive - Reject']
        );

        Modules::updateOrCreate(
            ['object' => 'good-receive-approve'],
            ['description' => 'Good Receive - Approve']
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
        
        //Assign All Module To Admin Role
        $role_admin = Role::where(DB::raw("LOWER(name)"), 'Super_Admin')->first();
        $modules    = Modules::all()->pluck('id');
        $role_admin->modules()->sync($modules);
    }
}
