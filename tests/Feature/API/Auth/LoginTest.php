<?php


namespace Tests\Feature\API\Auth;


use App\Models\AuthToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Laracore\Repository\ModelRepository;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    const SUCCESS_EMAIL = 'success@pinda.test';
    const SUCCESS_PASSWORD = 'secret';

    protected function setUp()
    {
        parent::setUp();

        factory(User::class)->create($this->getSuccessCredentials([
            'password' => \Hash::make(static::SUCCESS_PASSWORD)
        ]));
    }

    public function testLoginNoCredentials()
    {
        $response = $this->login();

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'email',
            'password'
        ]);
    }

    public function testLoginInvalidEmail()
    {
        $response = $this->login($this->getSuccessCredentials([
            'email' => 'invalidEmail@pinda.test'
        ]));

        $response->assertStatus(401);
    }

    public function testLoginInvalidPassword()
    {
        $response = $this->login($this->getSuccessCredentials([
            'password' => 'notSecret'
        ]));

        $response->assertStatus(401);
    }

    public function testLoginSuccess()
    {
        $response = $this->login($this->getSuccessCredentials());

        $response->assertSuccessful();
        $data = $response->json('data');

        $this->assertNotNull($data);
        $this->assertArrayHasKey('token', $data);

        $token = $data['token'];

        /** @var User $user */
        $user = User::query()
            ->join('auth_tokens', 'users.id', '=', 'auth_tokens.user_id')
            ->where('token', '=', $token)
            ->firstOrFail();

        $this->assertEquals(static::SUCCESS_EMAIL, $user->email);
    }

    public function testLogoutNoToken()
    {
        $response = $this->logout();

        $response->assertStatus(401);
    }

    public function testLogoutSuccess()
    {
        /** @var User $user */
        $user = User::query()
            ->where('email', '=', static::SUCCESS_EMAIL)
            ->firstOrFail();

        //TODO create token factory
        $token = new AuthToken();
        $token->token = '12345';
        $token->user()->associate($user);
        $token->save();

        //Ensure we can retrieve this user with token from the DB
        User::query()
            ->join('auth_tokens', 'users.id', '=', 'auth_tokens.user_id')
            ->where('token', '=', $token->token)
            ->whereNull('auth_tokens.deleted_at')
            ->where('users.id', '=', $user->id)
            ->firstOrFail();

        $response = $this->logout($token->token);
        $response->assertSuccessful();

        $shouldBeNull = User::query()
            ->join('auth_tokens', 'users.id', '=', 'auth_tokens.user_id')
            ->where('token', '=', $token->token)
            ->whereNull('auth_tokens.deleted_at')
            ->where('users.id', '=', $user->id)
            ->first();

        $this->assertNull($shouldBeNull);
    }

    private function getSuccessCredentials(array $overrides = []): array
    {
        return array_merge([
            'email' => static::SUCCESS_EMAIL,
            'password' => static::SUCCESS_PASSWORD,
        ], $overrides);
    }

    private function login(array $params = []): TestResponse
    {
        return $this->postJson(route('api.login'), $params);
    }

    private function logout(string $token = null): TestResponse
    {
        $headers = !is_null($token) ? ['X-Auth-Token' => $token] : [];

        return $this->deleteJson(route('api.logout'), [], $headers);
    }
}