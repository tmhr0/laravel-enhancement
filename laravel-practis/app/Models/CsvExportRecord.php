<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsvExportRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'download_user_id',
        'file_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'download_user_id');
    }
}
