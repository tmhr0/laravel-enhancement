<?php

namespace App\Http\Controllers;

use App\Models\CsvExportRecord;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $records = CsvExportRecord::with('user')->get();

        return view('users.csv-export-records.index', compact('records'));
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

        // 検索ワードに一致するユーザー名・会社名・部署名を取得
        $users = User::search($searchQuery, $searchOption)->get();

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

        //CsvExportRecordテーブルにデータを登録
        CsvExportRecord::create([
            'download_user_id' => Auth::id(),
            'file_name' => $file_name,
        ]);

        return Storage::download($path);
    }

    /**
     * @param $users
     * @return false|string
     */
    private function generateCsvData($users)
    {
        $header = ['ID', '名前', '所属会社', '所属部署'];
        $data = [$header];

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

    /**
     * @param $id
     * @return RedirectResponse|StreamedResponse
     */
    public function download($id): StreamedResponse|RedirectResponse
    {
        $record = CsvExportRecord::findOrFail($id);

        $file_path = 'csv/' . $record->file_name;

        if (Storage::exists($file_path)) {
            return Storage::download($file_path);
        }
        return redirect()->back()->withErrors(['error' => '該当ファイルが存在しません。']); // @codeCoverageIgnore
    }
}
