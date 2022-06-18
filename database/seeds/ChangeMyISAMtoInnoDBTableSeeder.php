<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChangeMyISAMtoInnoDBTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tables = DB::select('SHOW TABLES');

        foreach($tables as $table) {
            $table_name = 'Tables_in_'.env('DB_DATABASE');
            DB::statement('ALTER TABLE ' . $table->$table_name . ' ENGINE = InnoDB');
        }
    }
}
