<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\Section;
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

        $this->company = Company::factory()->create();
        $this->user = User::factory(['company_id' => $this->company->id])->create();
    }
    public function test_index()
    {
        $url = route('users.index');

        $this->get($url)->assertRedirect(route('login'));

        $response = $this->actingAs($this->user)->get($url);

        $response->assertStatus(200);
    }
}
