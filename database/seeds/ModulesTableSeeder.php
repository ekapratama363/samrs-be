<?php

use Illuminate\Database\Seeder;
use App\Models\Modules;

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



        // Location Type
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

        // Plant
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

        // Location
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

        // Storage
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

        // Supplier
        Modules::updateOrCreate(
            ['object' => 'supplier-view'],
            ['description' => 'Supplier - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'supplier-create'],
            ['description' => 'Supplier - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'supplier-update'],
            ['description' => 'Supplier - Edit']
        );

        // customer
        Modules::updateOrCreate(
            ['object' => 'customer-view'],
            ['description' => 'Customer - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'customer-create'],
            ['description' => 'Customer - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'customer-update'],
            ['description' => 'Customer - Edit']
        );

        // Uom
        Modules::updateOrCreate(
            ['object' => 'uom-view'],
            ['description' => 'Uom - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'uom-create'],
            ['description' => 'Uom - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'uom-update'],
            ['description' => 'Uom - Edit']
        );

        // Movement Type
        Modules::updateOrCreate(
            ['object' => 'movement-type-view'],
            ['description' => 'Movement Type - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'movement-type-create'],
            ['description' => 'Movement Type - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'movement-type-update'],
            ['description' => 'Movement Type - Edit']
        );

        // Procurement Group
        Modules::updateOrCreate(
            ['object' => 'procurement-group-view'],
            ['description' => 'Procurement Group - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'procurement-group-create'],
            ['description' => 'Procurement Group - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'procurement-group-update'],
            ['description' => 'Procurement Group - Edit']
        );

        // Material
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

        // Material vendor
        Modules::updateOrCreate(
            ['object' => 'material-vendor-view'],
            ['description' => 'Material vendor - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'material-vendor-create'],
            ['description' => 'Material vendor - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'material-vendor-update'],
            ['description' => 'Material vendor - Edit']
        );

        // Valuation Group
        Modules::updateOrCreate(
            ['object' => 'valuation-group-view'],
            ['description' => 'Asset Valuation Group - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'valuation-group-create'],
            ['description' => 'Asset Valuation Group - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'valuation-group-update'],
            ['description' => 'Asset Valuation Group - Edit']
        );

        // Asset Type
        Modules::updateOrCreate(
            ['object' => 'asset-type-view'],
            ['description' => 'Asset Type - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-type-create'],
            ['description' => 'Asset Type - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-type-update'],
            ['description' => 'Asset Type - Edit']
        );

        // Asset Status
        Modules::updateOrCreate(
            ['object' => 'asset-status-view'],
            ['description' => 'Asset Status - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-status-create'],
            ['description' => 'Asset Status - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-status-update'],
            ['description' => 'Asset Status - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-status-change'],
            ['description' => 'Asset Status - Change']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-status-activation'],
            ['description' => 'Asset Status - Activation']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-status-approval'],
            ['description' => 'Asset Status - Approval']
        );

        // Retired Reason
        Modules::updateOrCreate(
            ['object' => 'retired-reason-view'],
            ['description' => 'Asset Retired Reason - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'retired-reason-create'],
            ['description' => 'Asset Retired Reason - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'retired-reason-update'],
            ['description' => 'Asset Retired Reason - Edit']
        );

        // Asset
        Modules::updateOrCreate(
            ['object' => 'asset-view'],
            ['description' => 'Asset - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-create'],
            ['description' => 'Asset - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-update'],
            ['description' => 'Asset - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'asset-pdf'],
            ['description' => 'Asset - PDF']
        );

        // Company
        Modules::updateOrCreate(
            ['object' => 'company-view'],
            ['description' => 'Company - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'company-create'],
            ['description' => 'Company - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'company-update'],
            ['description' => 'Company - Edit']
        );

        // Department
        Modules::updateOrCreate(
            ['object' => 'department-view'],
            ['description' => 'Department - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'department-create'],
            ['description' => 'Department - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'department-update'],
            ['description' => 'Department - Edit']
        );

        // Cost Centers
        Modules::updateOrCreate(
            ['object' => 'cost-center-view'],
            ['description' => 'Cost Centers - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'cost-center-create'],
            ['description' => 'Cost Centers - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'cost-center-update'],
            ['description' => 'Cost Centers - Edit']
        );

        // Profit Center
        Modules::updateOrCreate(
            ['object' => 'profit-center-view'],
            ['description' => 'Profit Centers - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'profit-center-create'],
            ['description' => 'Profit Centers - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'profit-center-update'],
            ['description' => 'Profit Centers - Edit']
        );

        // MRP
        Modules::updateOrCreate(
            ['object' => 'mrp-view'],
            ['description' => 'Mrp Controller - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'mrp-create'],
            ['description' => 'Mrp Controller - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'mrp-update'],
            ['description' => 'Mrp Controller - Edit']
        );

        // Product Champion
        Modules::updateOrCreate(
            ['object' => 'product-champion-view'],
            ['description' => 'Product Champion - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'product-champion-create'],
            ['description' => 'Product Champion - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'product-champion-update'],
            ['description' => 'Product Champion - Edit']
        );

        // Storage Condition
        Modules::updateOrCreate(
            ['object' => 'storage-condition-view'],
            ['description' => 'Storage Condition - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'storage-condition-create'],
            ['description' => 'Storage Condition - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'storage-condition-update'],
            ['description' => 'Storage Condition - Edit']
        );

        // Storage Type
        Modules::updateOrCreate(
            ['object' => 'storage-type-view'],
            ['description' => 'Storage Type - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'storage-type-create'],
            ['description' => 'Storage Type - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'storage-type-update'],
            ['description' => 'Storage Type - Edit']
        );

        // Temp Condition
        Modules::updateOrCreate(
            ['object' => 'temp-condition-view'],
            ['description' => 'Temp Condition - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'temp-condition-create'],
            ['description' => 'Temp Condition - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'temp-condition-update'],
            ['description' => 'Temp Condition - Edit']
        );

        // Sales Organization
        Modules::updateOrCreate(
            ['object' => 'sales-organization-view'],
            ['description' => 'Sales Organization - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'sales-organization-create'],
            ['description' => 'Sales Organization - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'sales-organization-update'],
            ['description' => 'Sales Organization - Edit']
        );

        // Account Assignment
        Modules::updateOrCreate(
            ['object' => 'account-assignment-view'],
            ['description' => 'Account Assignment - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'account-assignment-create'],
            ['description' => 'Account Assignment - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'account-assignment-update'],
            ['description' => 'Account Assignment - Edit']
        );

        // Item Category
        Modules::updateOrCreate(
            ['object' => 'item-category-view'],
            ['description' => 'Item Category - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'item-category-create'],
            ['description' => 'Item Category - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'item-category-update'],
            ['description' => 'Item Category - Edit']
        );

        // Valuation Type
        Modules::updateOrCreate(
            ['object' => 'valuation-type-view'],
            ['description' => 'Valuation Type - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'valuation-type-create'],
            ['description' => 'Valuation Type - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'valuation-type-update'],
            ['description' => 'Valuation Type - Edit']
        );

        // Valuation Class
        Modules::updateOrCreate(
            ['object' => 'valuation-class-view'],
            ['description' => 'Valuation Class - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'valuation-class-create'],
            ['description' => 'Valuation Class - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'valuation-class-update'],
            ['description' => 'Valuation Class - Edit']
        );

        // Stock Determination
        Modules::updateOrCreate(
            ['object' => 'stock-determination-view'],
            ['description' => 'Stock Determination - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-determination-create'],
            ['description' => 'Stock Determination - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-determination-update'],
            ['description' => 'Stock Determination - Edit']
        );

        // Work Center
        Modules::updateOrCreate(
            ['object' => 'work-center-view'],
            ['description' => 'Work Center - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'work-center-create'],
            ['description' => 'Work Center - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'work-center-update'],
            ['description' => 'Work Center - Edit']
        );

        // Transport Type
        Modules::updateOrCreate(
            ['object' => 'transport-type-view'],
            ['description' => 'Transport Type - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'transport-type-create'],
            ['description' => 'Transport Type - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'transport-type-update'],
            ['description' => 'Transport Type - Edit']
        );

        // Incoterm
        Modules::updateOrCreate(
            ['object' => 'incoterm-view'],
            ['description' => 'Incoterm - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'incoterm-create'],
            ['description' => 'Incoterm - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'incoterm-update'],
            ['description' => 'Incoterm - Edit']
        );

        // Term of Payment
        Modules::updateOrCreate(
            ['object' => 'term-payment-view'],
            ['description' => 'Term of Payment - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'term-payment-create'],
            ['description' => 'Term of Payment - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'term-payment-update'],
            ['description' => 'Term of Payment - Edit']
        );

        // Chart of Account
        Modules::updateOrCreate(
            ['object' => 'chart-of-account-view'],
            ['description' => 'Chart of Account - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'chart-of-account-create'],
            ['description' => 'Chart of Account - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'chart-of-account-update'],
            ['description' => 'Chart of Account - Edit']
        );

        // Account Group
        Modules::updateOrCreate(
            ['object' => 'account-group-view'],
            ['description' => 'Account Group - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'account-group-create'],
            ['description' => 'Account Group - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'account-group-update'],
            ['description' => 'Account Group - Edit']
        );

        // Account
        Modules::updateOrCreate(
            ['object' => 'account-view'],
            ['description' => 'Account - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'account-create'],
            ['description' => 'Account - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'account-update'],
            ['description' => 'Account - Edit']
        );

        // Bom
        Modules::updateOrCreate(
            ['object' => 'bom-view'],
            ['description' => 'Bill of Material - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'bom-create'],
            ['description' => 'Bill of Material - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'bom-update'],
            ['description' => 'Bill of Material - Edit']
        );

        // Stock Overview
        Modules::updateOrCreate(
            ['object' => 'stock-overview'],
            ['description' => 'Stock Overview']
        );

        // Reservation
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
            ['object' => 'reservation-submit'],
            ['description' => 'Reservation - Submit for waiting approval']
        );

        Modules::updateOrCreate(
            ['object' => 'reservation-approval'],
            ['description' => 'Reservation - Approve or Reject']
        );

        // Purchase Requisition
        Modules::updateOrCreate(
            ['object' => 'purchase-requisition-view'],
            ['description' => 'Purchase Requisition - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'purchase-requisition-create'],
            ['description' => 'Purchase Requisition - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'purchase-requisition-update'],
            ['description' => 'Purchase Requisition - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'purchase-requisition-submit'],
            ['description' => 'Purchase Requisition - Submit for waiting approval']
        );

        Modules::updateOrCreate(
            ['object' => 'purchase-requisition-approval'],
            ['description' => 'Purchase Requisition - Approve or Reject']
        );

        // Goods Movement
        Modules::updateOrCreate(
            ['object' => 'goods-movement-view'],
            ['description' => 'Goods Movement - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'goods-movement-create'],
            ['description' => 'Goods Movement - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'goods-movement-update'],
            ['description' => 'Goods Movement - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'goods-movement-reversal'],
            ['description' => 'Goods Movement - Reversal']
        );

        // History Movement
        Modules::updateOrCreate(
            ['object' => 'history-movement-view'],
            ['description' => 'History Movement - Display']
        );

        // Picking List
        Modules::updateOrCreate(
            ['object' => 'picking-list-view'],
            ['description' => 'Picking List - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'picking-list-update'],
            ['description' => 'Picking List - Update as DO / PR']
        );

        Modules::updateOrCreate(
            ['object' => 'picking-list-reject'],
            ['description' => 'Picking List - Reject']
        );

        // Delivery Order
        Modules::updateOrCreate(
            ['object' => 'delivery-orders-view'],
            ['description' => 'Delivery Order - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'delivery-orders-update'],
            ['description' => 'Delivery Order - Update']
        );

        Modules::updateOrCreate(
            ['object' => 'delivery-orders-send'],
            ['description' => 'Delivery Order - Send']
        );

        Modules::updateOrCreate(
            ['object' => 'delivery-orders-pdf'],
            ['description' => 'Delivery Order - Print PDF']
        );

        // Purchase Order
        Modules::updateOrCreate(
            ['object' => 'purchase-orders-view'],
            ['description' => 'Purchase Order - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'purchase-orders-update'],
            ['description' => 'Purchase Order - Update']
        );

        Modules::updateOrCreate(
            ['object' => 'purchase-orders-approval'],
            ['description' => 'Purchase Order - Approval']
        );

        Modules::updateOrCreate(
            ['object' => 'purchase-orders-pdf'],
            ['description' => 'Purchase Order - Print PDF']
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

        // Stock Opname
        Modules::updateOrCreate(
            ['object' => 'stock-opname-view'],
            ['description' => 'Stock Opname - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-opname-update'],
            ['description' => 'Stock Opname - Update']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-opname-create'],
            ['description' => 'Stock Opname - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-opname-submit'],
            ['description' => 'Stock Opname - Submit']
        );

        Modules::updateOrCreate(
            ['object' => 'stock-opname-approve'],
            ['description' => 'Stock Opname - Approve']
        );

        // Production Order
        Modules::updateOrCreate(
            ['object' => 'production-order-view'],
            ['description' => 'Production Order - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'production-order-upload'],
            ['description' => 'Production Order - Upload']
        );

        Modules::updateOrCreate(
            ['object' => 'production-order-download'],
            ['description' => 'Production Order - Download']
        );

        Modules::updateOrCreate(
            ['object' => 'production-order-confirm'],
            ['description' => 'Production Order - Confirm']
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

        // Request for Quotation
        Modules::updateOrCreate(
            ['object' => 'rfq-view'],
            ['description' => 'Request for Quotation - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'rfq-create'],
            ['description' => 'Request for Quotation - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'rfq-update'],
            ['description' => 'Request for Quotation - Edit']
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

        // project
        Modules::updateOrCreate(
            ['object' => 'project-view'],
            ['description' => 'project - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'project-create'],
            ['description' => 'project - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'project-update'],
            ['description' => 'project - Edit']
        );

        // budget
        Modules::updateOrCreate(
            ['object' => 'budget-view'],
            ['description' => 'Budget - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'budget-create'],
            ['description' => 'Budget - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'budget-update'],
            ['description' => 'Budget - Edit']
        );

        // depreciation group
        Modules::updateOrCreate(
            ['object' => 'depre-group-view'],
            ['description' => 'depreciation group - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'depre-group-create'],
            ['description' => 'depreciation group - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'depre-group-update'],
            ['description' => 'depreciation group - Edit']
        );

        // depreciation type
        Modules::updateOrCreate(
            ['object' => 'depre-type-view'],
            ['description' => 'depreciation type - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'depre-type-create'],
            ['description' => 'depreciation type - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'depre-type-update'],
            ['description' => 'depreciation type - Edit']
        );

        Modules::updateOrCreate(
            ['object' => 'budget-approval'],
            ['description' => 'Budget - Approval']
        );

        // valuation asset
        Modules::updateOrCreate(
            ['object' => 'valuation-asset-view'],
            ['description' => 'valuation asset - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'valuation-asset-create'],
            ['description' => 'valuation asset - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'valuation-asset-update'],
            ['description' => 'valuation asset - Edit']
        );

        // vendor group
        Modules::updateOrCreate(
            ['object' => 'vendor-group-view'],
            ['description' => 'Vendor group - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'vendor-group-create'],
            ['description' => 'Vendor group - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'vendor-group-update'],
            ['description' => 'Vendor group - Edit']
        );

        // monthly consumption
        Modules::updateOrCreate(
            ['object' => 'monthly-consumption-view'],
            ['description' => 'Monthly Consumption - Display']
        );

        Modules::updateOrCreate(
            ['object' => 'monthly-consumption-download'],
            ['description' => 'Monthly Consumption - Download']
        );


        // Task List
        Modules::updateOrCreate(
            ['object' => 'task-list-view'],
            ['description' => 'Task List - View']
        );

        Modules::updateOrCreate(
            ['object' => 'task-list-update'],
            ['description' => 'Task List - Update']
        );

        Modules::updateOrCreate(
            ['object' => 'task-list-create'],
            ['description' => 'Task List - Create']
        );


        // Service Request
        Modules::updateOrCreate(
            ['object' => 'service-request-view'],
            ['description' => 'Service Request - View']
        );

        Modules::updateOrCreate(
            ['object' => 'service-request-update'],
            ['description' => 'Service Request - Update']
        );

        Modules::updateOrCreate(
            ['object' => 'service-request-create'],
            ['description' => 'Service Request - Create']
        );


        // Corrective Maintenance
        Modules::updateOrCreate(
            ['object' => 'corrective-maintenance-view'],
            ['description' => 'Corrective Maintenance - View']
        );

        Modules::updateOrCreate(
            ['object' => 'corrective-maintenance-update'],
            ['description' => 'Corrective Maintenance - Update']
        );

        Modules::updateOrCreate(
            ['object' => 'corrective-maintenance-create'],
            ['description' => 'Corrective Maintenance - Create']
        );


        // Preventive Maintenance
        Modules::updateOrCreate(
            ['object' => 'preventive-maintenance-view'],
            ['description' => 'Preventive Maintenance - View']
        );

        Modules::updateOrCreate(
            ['object' => 'preventive-maintenance-update'],
            ['description' => 'Preventive Maintenance - Update']
        );

        Modules::updateOrCreate(
            ['object' => 'preventive-maintenance-create'],
            ['description' => 'Preventive Maintenance - Create']
        );


        // Work Order
        Modules::updateOrCreate(
            ['object' => 'work-order-view'],
            ['description' => 'Work Order - View']
        );

        Modules::updateOrCreate(
            ['object' => 'work-order-update'],
            ['description' => 'Work Order - Update']
        );

        Modules::updateOrCreate(
            ['object' => 'work-order-create'],
            ['description' => 'Work Order - Create']
        );

        // Incoming Invoice
        Modules::updateOrCreate(
            ['object' => 'incoming-invoice-create'],
            ['description' => 'Incoming Invoice - Create']
        );

        Modules::updateOrCreate(
            ['object' => 'incoming-invoice-view'],
            ['description' => 'Incoming Invoice - View']
        );

        // Accounting Procedure
        Modules::updateOrCreate(
            ['object' => 'accounting-procedure-view'],
            ['description' => 'Accounting Procedure - View']
        );

        Modules::updateOrCreate(
            ['object' => 'accounting-procedure-update'],
            ['description' => 'Accounting Procedure - Update']
        );

        Modules::updateOrCreate(
            ['object' => 'accounting-procedure-create'],
            ['description' => 'Accounting Procedure - Create']
        );

        //Assign All Module To Admin Role
        $role_admin = Role::where(DB::raw("LOWER(name)"), 'admin')->first();
        $modules    = Modules::all()->pluck('id');
        $role_admin->modules()->sync($modules);
    }
}
