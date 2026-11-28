<?php

namespace Tests\Feature\Api;

use App\Models\Gender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_signup_and_receive_token(): void
    {
        $gender = Gender::factory()->create(['name' => 'Мужской']);

        $response = $this->postJson('/api/v1/auth/signup', [
            'fio' => 'Ivanov Ivan',
            'email' => 'ivanov@ivan.kz',
            'password' => 'asdf1234',
            'birthday' => '2006-04-15',
            'gender_id' => $gender->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('fio', 'Ivanov Ivan')
            ->assertJsonStructure(['status', 'token', 'id', 'fio']);

        $this->assertDatabaseHas('users', [
            'email' => 'ivanov@ivan.kz',
            'fio' => 'Ivanov Ivan',
        ]);
    }

    public function test_signup_validates_input(): void
    {
        $response = $this->postJson('/api/v1/auth/signup', [
            'fio' => 'A',
            'email' => 'not-an-email',
            'password' => '123',
            'birthday' => 'invalid-date',
            'gender_id' => 999,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fio', 'email', 'password', 'birthday', 'gender_id']);
    }

    public function test_user_can_signin_and_wrong_credentials_return_401(): void
    {
        $user = User::factory()->create([
            'fio' => 'Ivanov Ivan',
            'email' => 'ivanov@ivan.kz',
            'password' => 'asdf1234',
        ]);

        $success = $this->postJson('/api/v1/auth/signin', [
            'email' => 'ivanov@ivan.kz',
            'password' => 'asdf1234',
        ]);

        $success->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('fio', 'Ivanov Ivan')
            ->assertJsonStructure(['status', 'token', 'id', 'fio']);

        $wrongPassword = $this->postJson('/api/v1/auth/signin', [
            'email' => 'ivanov@ivan.kz',
            'password' => 'wrongpass',
        ]);

        $wrongPassword->assertUnauthorized()
            ->assertExactJson([
                'status' => 'invalid',
                'message' => 'Wrong email or password',
            ]);
    }

    public function test_user_can_signout_and_token_is_revoked(): void
    {
        $user = User::factory()->create([
            'email' => 'ivanov@ivan.kz',
            'password' => 'asdf1234',
        ]);

        $token = $this->postJson('/api/v1/auth/signin', [
            'email' => 'ivanov@ivan.kz',
            'password' => 'asdf1234',
        ])->json('token');

        $signout = $this->withToken($token)->postJson('/api/v1/auth/signout');
        $signout->assertOk()
            ->assertExactJson(['status' => 'success']);

        $afterSignout = $this->withToken($token)->getJson("/api/v1/users/{$user->id}");
        $afterSignout->assertUnauthorized();
    }
}
