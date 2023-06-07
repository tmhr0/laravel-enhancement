<?php

namespace Database\Seeders;

use App\Models\CsvExportRecord;
use Illuminate\Database\Seeder;

class CsvExportRecordSeeder extends Seeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        CsvExportRecord::factory()->count(10)->create();
    }
}
