<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportRecordController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        return view('users.csv-export-records.index');
    }

    /**
     * @param Request $request
     * @return StreamedResponse
     */
    public function store(Request $request)
    {
        // 検索キーワードと検索オプションを取得
        $searchQuery = $request->input('search');
        $searchOption = $request->input('search_option');

        // ユーザーデータを検索
        $query = User::query();

        // 検索ワードに一致するユーザー名・会社名・部署名を取得
        if ($searchOption === 'user') {
            $users = $query->where('name', 'like', '%' . $searchQuery . '%');
        } elseif ($searchOption === 'company') {
            $users = $query->whereHas('company', function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            });
        } elseif ($searchOption === 'section') {
            $users = $query->whereHas('sections', function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            });
        }
        $users = $query->get();

        $file_name = sprintf('users-%s.csv', now()->format('YmdHis'));
        $csvData = $this->generateCsvData($users);

        // Storage/app/csv でcsvディレクトリが存在しない場合は作成する
        $directory = 'csv';
        $path = $directory . '/' . $file_name;

        if (!File::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        // file_put_contents() を使用してCSVデータをファイルに書き込む
        $file_path = storage_path('app/csv/' . $file_name);
        file_put_contents($file_path, $csvData);

        return Storage::download($path);
    }

    /**
     * @param $users
     * @return false|string
     */
    private function generateCsvData($users)
    {
        $header = ['ID', '名前', '所属会社', '所属部署'];
        $data = [];

        // ヘッダーデータを追加
        $data[] = $header;

        // ユーザーデータを追加
        foreach ($users as $user) {
            $rowData = [
                $user->id,
                $user->name,
                $user->company->name,
                $user->sections->implode('name', ', '),
            ];
            $data[] = $rowData;
        }

        // CSVデータを生成して文字列として返す
        $output = fopen('php://temp', 'w');
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        rewind($output);

        $csvData = stream_get_contents($output);
        fclose($output);

        return $csvData;
    }
}
