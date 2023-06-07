<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\Section;
use App\Policies\UserPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
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

    public function test_index()
    {
        $url = route('users.index');

        $this->get($url)->assertRedirect(route('login'));

        $response = $this->actingAs($this->user)->get($url);

        $response->assertStatus(200);

        // ユーザーのroleがuserの場合のページ切り替え
        $url = route('users.index');
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
        $url = route('users.index');
        $response = $this->actingAs($this->admin)->get($url);

        // レスポンスに制限メッセージが含まれないことを確認
        $response->assertDontSee('このページはユーザー権限での閲覧が制限されています');
    }

    public function test_search_results_display(): void
    {
        // ユーザー名で検索をした時、ユーザー名・会社名・部署名が表示される
        $url = route('users.index', [
            'search' => 'サンプルアドミン',
            'search_option' => 'user',
        ]);
        $response = $this->actingAs($this->admin)->get($url)->assertStatus(200);

        $response->assertSee('サンプルアドミン');
        $response->assertSee('アドミン会社');
        $response->assertSee('アドミン部署');

        //検索結果に対象外の会社名・部署名は表示されない。
        $response->assertDontSee('サンプルユーザー');
        $response->assertDontSee('ユーザー会社');
        $response->assertDontSee('ユーザー部署');

        // 会社名で検索をした時、ユーザー名・会社名・部署名が表示される
        $url = route('users.index', [
            'search' => 'アドミン会社',
            'search_option' => 'company',
        ]);
        $response = $this->actingAs($this->admin)->get($url)->assertStatus(200);

        $response->assertSee('サンプルアドミン');
        $response->assertSee('アドミン会社');
        $response->assertSee('アドミン部署');

        //検索結果に対象外の会社名・部署名は表示されない。
        $response->assertDontSee('サンプルユーザー');
        $response->assertDontSee('ユーザー会社');
        $response->assertDontSee('ユーザー部署');

        // 部署名で検索をした時、ユーザー名・会社名・部署名が表示される
        $url = route('users.index', [
            'search' => 'アドミン部署',
            'search_option' => 'section',
        ]);

        $response = $this->actingAs($this->admin)->get($url)->assertStatus(200);

        $response->assertSee('サンプルアドミン');
        $response->assertSee('アドミン会社');
        $response->assertSee('アドミン部署');

        //検索結果に対象外の会社名・部署名は表示されない。
        $response->assertDontSee('サンプルユーザー');
        $response->assertDontSee('ユーザー会社');
        $response->assertDontSee('ユーザー部署');
    }
}
