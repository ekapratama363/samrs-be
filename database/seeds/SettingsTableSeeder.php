<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::firstOrCreate(
            ['key' => 'COMPANY_LOGO'],
            ['key' => 'COMPANY_LOGO', 'value' => 'assets/images/sinarmas.png', 'sort' => 0]
        );
        Setting::firstOrCreate(
            ['key' => 'COMPANY_NAME'],
            ['key' => 'COMPANY_NAME', 'value' => 'PT. BANK SINARMAS Tbk.', 'sort' => 1]
        );
        Setting::firstOrCreate(
            ['key' => 'COMPANY_ADDRESS'],
            [
                'key' => 'COMPANY_ADDRESS',
                'value' => 'Roxy Square, Jl. Kyai Tapa No. 1, Unit D3, Lower Ground, Grogol Petamburan, Jakarta Barat 11450',
                'sort' => 2
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'COMPANY_PHONE'],
            ['key' => 'COMPANY_PHONE', 'value' => '021 - 56954567', 'sort' => 3]
        );
        Setting::firstOrCreate(
            ['key' => 'COMPANY_FAX'],
            ['key' => 'COMPANY_FAX', 'value' => '021 - 56954545', 'sort' => 4]
        );
        Setting::firstOrCreate(
            ['key' => 'THEME'],
            ['key' => 'THEME', 'value' => '{"name": "Cold Sapphire", "primary_color": "#0073dd", "secondary_color": "#1890ff"}', 'sort' => 5]
        );
        Setting::firstOrCreate(
            ['key' => 'PASS_LENGTH_MIN'],
            ['key' => 'PASS_LENGTH_MIN', 'value' => '8', 'sort' => 6]
        );
        Setting::firstOrCreate(
            ['key' => 'HISTORY_PASS_KEEP'],
            ['key' => 'HISTORY_PASS_KEEP', 'value' => '3', 'sort' => 7]
        );
        Setting::firstOrCreate(
            ['key' => 'PASS_CYCLE_LIMIT'],
            ['key' => 'PASS_CYCLE_LIMIT', 'value' => '90', 'sort' => 8]
        );
        Setting::firstOrCreate(
            ['key' => 'PASS_REGEX'],
            ['key' => 'PASS_REGEX', 'value' => "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).+$/", 'sort' => 9]
        );
        Setting::firstOrCreate(
            ['key' => 'PASS_REGEX_DESCRIPTION'],
            [
                'key' => 'PASS_REGEX_DESCRIPTION',
                'value' => 'Alphanumeric characters\nUppercase characters of European languages (A through Z characters)\nLowercase characters of European languages',
                'sort' => 10
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'LOGIN_FAILED_LIMIT'],
            ['key' => 'LOGIN_FAILED_LIMIT', 'value' => '5', 'sort' => 11]
        );
        Setting::firstOrCreate(
            ['key' => 'LAST_ACTIVITY'],
            ['key' => 'LAST_ACTIVITY', 'value' => '30', 'sort' => 12]
        );
        Setting::firstOrCreate(
            ['key' => 'PAGINATION_DEFAULT'],
            ['key' => 'PAGINATION_DEFAULT', 'value' => '25', 'sort' => 13]
        );
        Setting::firstOrCreate(
            ['key' => 'EXPIRY_BATCH'],
            ['key' => 'EXPIRY_BATCH', 'value' => '1', 'sort' => 14]
        );
        Setting::firstOrCreate(
            ['key' => 'SORT_BATCH'],
            ['key' => 'SORT_BATCH', 'value' => '0', 'sort' => 15]
        );
        Setting::firstOrCreate(
            ['key' => 'PROPOSE_DELIV_DATE'],
            ['key' => 'PROPOSE_DELIV_DATE', 'value' => '2', 'sort' => 16]
        );
        Setting::firstOrCreate(
            ['key' => 'MAX_GR_STO'],
            ['key' => 'MAX_GR_STO', 'value' => '1', 'sort' => 17]
        );
        Setting::firstOrCreate(
            ['key' => 'HALAL_CERTIFIED'],
            ['key' => 'HALAL_CERTIFIED', 'value' => '1', 'sort' => 18]
        );
        Setting::firstOrCreate(
            ['key' => 'USER_INACTIVE_LIMIT'],
            ['key' => 'USER_INACTIVE_LIMIT', 'value' => '90', 'sort' => 19]
        );
        Setting::firstOrCreate(
            ['key' => 'ASSET_DEPRECIATION_DATE'],
            ['key' => 'ASSET_DEPRECIATION_DATE', 'value' => '15', 'sort' => 20]
        );
        Setting::firstOrCreate(
            ['key' => 'UNIT_COST_WITH_TAX'],
            ['key' => 'UNIT_COST_WITH_TAX', 'value' => '0', 'sort' => 21]
        );
        Setting::firstOrCreate(
            ['key' => 'WEB_URL'],
<<<<<<< HEAD
            ['key' => 'WEB_URL', 'value' => 'http://localhost:8000/#', 'sort' => 22]
=======
            ['key' => 'WEB_URL', 'value' => 'https://izora.mindaperdana.com/#', 'sort' => 22]
>>>>>>> master
        );
        Setting::firstOrCreate(
            ['key' => 'VENDOR_INVOICE_DIFF'],
            ['key' => 'VENDOR_INVOICE_DIFF', 'value' => '10000', 'sort' => 23]
        );
    }
}
