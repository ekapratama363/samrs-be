<?php

use Illuminate\Database\Seeder;
use App\Models\ClassificationType;

class ClassificationTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClassificationType::updateOrCreate(
            ['name' => 'User_Profile'],
            ['deleted' => false]
        );

        ClassificationType::updateOrCreate(
            ['name' => 'Material'],
            ['deleted' => false]
        );

        ClassificationType::updateOrCreate(
            ['name' => 'Quality_Profile'],
            ['deleted' => false]
        );

        ClassificationType::updateOrCreate(
            ['name' => 'Workflow_Approval'],
            ['deleted' => false]
        );
    }
}
