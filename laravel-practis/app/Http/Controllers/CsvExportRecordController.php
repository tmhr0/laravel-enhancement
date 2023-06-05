<?php

namespace App\Http\Controllers;

use App\Models\CsvExportRecord;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CsvExportRecordController extends Controller
{
    public function index(): View
    {
        return view('users.csv-export-records.index');
    }

    public function store(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // 検索キーワードと検索オプションを取得
        $searchQuery = $request->input('search');
        $searchOption = $request->input('search_option');

        // ユーザーデータを検索
        $users = User::query();

        // 検索結果から一致するユーザー名・会社名・部署名を$usersを取得
        if ($searchOption === 'user') {
            $users = $users->where('name', 'like', '%' . $searchQuery . '%');
        } elseif ($searchOption === 'company') {
            $users = $users->whereHas('company', function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            });
        } elseif ($searchOption === 'section') {
            $users = $users->whereHas('sections', function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            });
        }
        $users = $users->get();

        $file_name = sprintf('users-%s.csv', now()->format('YmdHis'));
        $csvData = $this->generateCsvData($users);

        // file_put_contents() を使用してCSVデータをファイルに書き込む
        file_put_contents(public_path($file_name), $csvData);

        return response()->download(public_path($file_name));
    }

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
