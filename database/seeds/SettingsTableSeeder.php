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
        $data = $this->defaultSetting();
        foreach ($data as $key => $value) {
            Setting::firstOrCreate($value);
        }
    }

    public function defaultSetting()
    {
        return [
            ['key' => 'COMPANY_LOGO', 'value' => '', 'sort' => 0],
            ['key' => 'COMPANY_NAME', 'value' => '', 'sort' => 1],
            [
                'key' => 'COMPANY_ADDRESS',
                'value' => '',
                'sort' => 2
            ],
            ['key' => 'COMPANY_PHONE', 'value' => '', 'sort' => 3],
            ['key' => 'COMPANY_FAX', 'value' => '', 'sort' => 4],
            ['key' => 'THEME', 'value' => '{"name": "Cold Sapphire", "primary_color": "#0073dd", "secondary_color": "#1890ff"}', 'sort' => 5],
            ['key' => 'PASS_LENGTH_MIN', 'value' => '8', 'sort' => 6],
            ['key' => 'HISTORY_PASS_KEEP', 'value' => '', 'sort' => 7],
            ['key' => 'PASS_CYCLE_LIMIT', 'value' => '90', 'sort' => 8],
            [
                'key' => 'PASS_REGEX',
                'value' => "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).+$/",
                'sort' => 9
            ],
            [
                'key' => 'PASS_REGEX_DESCRIPTION',
                'value' => 'Alphanumeric characters\nUppercase characters of European languages (A through Z characters)\nLowercase characters of European languages',
                'sort' => 10
            ],
            ['key' => 'LOGIN_FAILED_LIMIT', 'value' => '5', 'sort' => 11],
            ['key' => 'LAST_ACTIVITY', 'value' => '30', 'sort' => 12],
            ['key' => 'PAGINATION_DEFAULT', 'value' => '25', 'sort' => 13],
            //['key' => 'EXPIRY_BATCH', 'value' => '1', 'sort' => 14],
            //['key' => 'SORT_BATCH', 'value' => '0', 'sort' => 15],
            //['key' => 'PROPOSE_DELIV_DATE', 'value' => '', 'sort' => 16],
            //['key' => 'MAX_GR_STO', 'value' => '', 'sort' => 17],
            //['key' => 'HALAL_CERTIFIED', 'value' => '', 'sort' => 18],
            ['key' => 'USER_INACTIVE_LIMIT', 'value' => '90', 'sort' => 19],
            //['key' => 'ASSET_DEPRECIATION_DATE', 'value' => '', 'sort' => 20],
            //['key' => 'UNIT_COST_WITH_TAX', 'value' => '', 'sort' => 21],
            ['key' => 'WEB_URL', 'value' => 'https://izora.mindaperdana.com/#', 'sort' => 22],
            //['key' => 'VENDOR_INVOICE_DIFF', 'value' => '', 'sort' => 23],
        ];
    }

}
