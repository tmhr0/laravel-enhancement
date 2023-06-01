<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CsvExportRecordController extends Controller
{
    public function index(): View
    {
        return view('users.csv-export-records.index');
    }
}
