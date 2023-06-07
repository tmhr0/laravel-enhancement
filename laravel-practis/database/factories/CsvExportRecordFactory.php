<?php

namespace Database\Factories;

use App\Models\CsvExportRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CsvExportRecord>
 */
class CsvExportRecordFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = CsvExportRecord::class;

    public function definition()
    {
        $timestamp = now()->format('YmdHis');
        $fileName = 'users-' . $timestamp . '.csv';

        return [
            'download_user_id' => User::factory(),
            'file_name' => $fileName,
        ];
    }
}
