<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

        $response = $this->actingAs($this->user)->get($url);

        $response->assertStatus(200);
    }


    public function test_download(){
        //CSVファイルのダウンロードが正常に働く
        //CSVダウンロードボタンをクリックした場合、指定のファイル名でダウンロードされていることを確認する。

        //空の検索クエリの場合:
        //検索クエリが空の場合、すべてのユーザーがCSVファイルに含まれることを確認する。

        //ダウンロードしたCSVファイルを確認し、内容が予想どおりであることを確認する

        //ユーザー名で検索した場合、一致するユーザーがCSVファイルに含まれることを確認する。
        //会社名で検索した場合、一致する会社のみがCSVファイルに含まれることを確認する。
        //部署名で検索した場合、一致する部署のみがCSVファイルに含まれることを確認する。
    }
}
