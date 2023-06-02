<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CsvExportRecordController extends Controller
{
    public function index(): View
    {
        return view('users.csv-export-records.index');
    }

    public function store(Request $request): BinaryFileResponse
    {
        // セッションから$usersを取得
        $users = session()->get('users');
        $file_name = sprintf('users-%s.csv', now()->format('YmdHis'));
        $csvData = $this->generateCsvData($users);

        //file_put_contents()  指定されたファイルにデータを書き込むために使われる
        //第一引数に書き込むファイルのパスを、第二引数には書き込むデータを指定する
        //public_path()  public ディレクトリ内にファイルを保存するために使われる
        file_put_contents(public_path($file_name), $csvData);

        return Response::download(public_path($file_name));
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
