<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvExportRecordControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory(['company_id' => $this->company->id])->create();
    }

    public function test_index()
    {
        $url = route('users.csv-export-records.index');

        $this->get($url)->assertRedirect(route('login'));

        $response = $this->actingAs($this->user)->get($url);

        $response->assertStatus(200);
    }

    public function test_store()
    {
        $url = route('users.csv-export-records.store');

        $this->get($url)->assertRedirect(route('login'));

        // CsvExportRecordテーブルにデータが追加されることを確認する
        $response = $this->actingAs($this->user)->post(route('users.csv-export-records.store'));

        $response->assertStatus(200);

        $this->assertDatabaseHas('csv_export_records', [
            'download_user_id' => $this->user->id,
            'file_name' => 'users-' . now()->format('YmdHis') . '.csv',
        ]);
    }

    public function test_download()
    {
        $url = route('users.csv-export-records.store');

        $this->get($url)->assertRedirect(route('login'));

        $requestData = [
            'search' => '', // 検索クエリを空にする
            'search_option' => 'user', // ユーザー名を検索オプションとして選択
        ];

        // リクエストを送信
        $response = $this->actingAs($this->user)->post($url, $requestData);

        //CSVファイルのダウンロードが正常に働く
        // レスポンスを検証
        $response->assertStatus(200);
        $response->assertDownload();

        //指定のディレクトリにCSVがダウンロードされている
        $file_name = 'users-' . now()->format('YmdHis') . '.csv';
        $file_path = 'csv/' . $file_name;
        $this->assertTrue(Storage::disk('local')->exists($file_path));

        //指定のファイル名でCSVダウンロードされている
        $response->assertHeader('content-disposition', 'attachment; filename=' . $file_name);
    }
}
