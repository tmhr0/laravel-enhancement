<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\CsvExportRecord;
use App\Models\Section;
use App\Models\User;
use App\Policies\UserPolicy;
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
            'name' => 'ユーザー会社'
        ]);

        $this->user = User::factory([
            'company_id' => $this->company->id,
            'name' => 'サンプルユーザー',
            'role' => 'user',
        ])->create();

        $this->section = Section::factory([
            'company_id' => $this->company->id,
            'name' => 'ユーザー部署'
        ])->create();

        $this->section->users()->attach($this->user->id);

        $this->company2 = Company::factory()->create([
            'name' => 'アドミン会社'
        ]);

        $this->admin = User::factory([
            'company_id' => $this->company2->id,
            'name' => 'サンプルアドミン',
            'role' => 'admin',
        ])->create();

        $this->section2 = Section::factory([
            'company_id' => $this->company2->id,
            'name' => 'アドミン部署'
        ])->create();

        $this->section2->users()->attach($this->admin->id);
    }

    public function test_model_attributes()
    {
        // テスト用のデータを用意
        $attributes = [
            'download_user_id' => 1,
            'file_name' => 'test.csv',
            'file_path' => 'app/csv/file.csv',
        ];

        $csvExportRecord = new CsvExportRecord($attributes);

        // 属性が正しく設定されているかをアサーション
        $this->assertEquals($attributes['download_user_id'], $csvExportRecord->download_user_id);
        $this->assertEquals($attributes['file_name'], $csvExportRecord->file_name);
        $this->assertEquals($attributes['file_path'], $csvExportRecord->file_path);
    }

    public function test_relation()
    {
        $user = User::factory()->create();

        $recordData = [
            'download_user_id' => $user->id,
            'file_name' => 'test.csv',
        ];

        $record = CsvExportRecord::create($recordData);

        $this->assertInstanceOf(User::class, $record->user);
        $this->assertEquals($user->id, $record->user->id);
    }

    public function test_csv_index()
    {
        // ユーザーのroleがuserの場合のページ切り替え
        $url = route('users.csv-export-records.index');
        $response = $this->actingAs($this->user)->get($url);

        // レスポンスに制限メッセージが含まれることを確認
        $response->assertSee('このページはユーザー権限での閲覧が制限されています');

        // UserPolicyのuserAccessメソッドを呼び出して認可拒否を確認する
        $user = User::factory()->create();

        $policy = new UserPolicy();
        $result = $policy->userAccess($user);

        $this->assertFalse($result);

        // ユーザーのroleがadminの場合のページ切り替え
        // ログインしてテスト用のページにアクセス
        $url = route('users.csv-export-records.index');
        $response = $this->actingAs($this->admin)->get($url);

        // レスポンスに制限メッセージが含まれないことを確認
        $response->assertDontSee('このページはユーザー権限での閲覧が制限されています');
    }
    public function test_csv_records(){

        //事前にテスト用のCSVデータを作成する
        //ファイルパス先にcsvファイルが存在しているか確認
        //再DLできる場合、DLが完了したか確認
        //再DLできる場合、ファイル名が一致しているか確認
        //再DLできない場合、エラーメッセージの確認
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
