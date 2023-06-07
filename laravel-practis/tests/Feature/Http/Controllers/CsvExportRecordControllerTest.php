<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\CsvExportRecord;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\QueryException;
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

        $this->company = Company::factory()->create([
            'name' => 'アドミン会社'
        ]);

        $this->user = User::factory([
            'company_id' => $this->company->id,
            'name' => 'サンプルアドミン',
            'role' => 'admin',
        ])->create();

        $this->section = Section::factory([
            'company_id' => $this->company->id,
            'name' => 'アドミン部署'
        ])->create();

        $this->section->users()->attach($this->user->id);
    }

    public function testUserRelation()
    {
        // テスト用のCsvExportRecordインスタンスを作成
        $csvExportRecord = CsvExportRecord::factory()->create();

        // 関連するUserモデルを取得
        $user = $csvExportRecord->user;

        // 関連するUserモデルが正しく取得できたかをアサーション
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($csvExportRecord->download_user_id, $user->id);
    }

    public function testModelAttributes()
    {
        // テスト用のデータを用意
        $attributes = [
            'download_user_id' => 1,
            'file_name' => 'test.csv',
            'file_path' => '/path/to/file.csv',
        ];

        // CsvExportRecordインスタンスを作成
        $csvExportRecord = new CsvExportRecord($attributes);

        // 属性が正しく設定されているかをアサーション
        $this->assertEquals($attributes['download_user_id'], $csvExportRecord->download_user_id);
        $this->assertEquals($attributes['file_name'], $csvExportRecord->file_name);
        $this->assertEquals($attributes['file_path'], $csvExportRecord->file_path);
    }

    public function testCsvExportRecordUserRelationship()
    {
        $user = User::factory()->create();

        $record = CsvExportRecord::factory()->create([
            'download_user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $record->user);
        $this->assertEquals($user->id, $record->user->id);
    }

    public function testCsvExportRecordForeignKeyConstraint()
    {
        $this->expectException(QueryException::class);

        $recordData = [
            'download_user_id' => 999, // 存在しないユーザーID
            'file_name' => 'test.csv',
        ];

        CsvExportRecord::create($recordData);
    }


    public function test_index()
    {
        // テスト用のダミーデータを作成
        CsvExportRecord::factory()->count(3)->create();

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
