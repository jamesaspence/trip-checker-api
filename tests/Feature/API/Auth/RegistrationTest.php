<?php


namespace Tests\Feature\API\Auth;


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    const SUCCESS_FIRST_NAME = 'Test';
    const SUCCESS_LAST_NAME = 'Testerton';
    const SUCCESS_EMAIL = 'success@pinda.test';
    const SUCCESS_PASSWORD = 'secret';

    public function testRegistrationNoCredentials()
    {
        $response = $this->register();

        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'email',
            'password'
        ]);
    }

    public function testRegistrationInvalidEmail()
    {
        $response = $this->register($this->getSuccessCredentials([
            'email' => 'invalidEmail'
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'email'
        ]);
        $response->assertJsonMissingValidationErrors([
            'first_name',
            'last_name',
            'password'
        ]);
    }

    public function testRegistrationNoPasswordConfirmation()
    {
        $params = $this->getSuccessCredentials();
        unset($params['password_confirmation']);

        $response = $this->register($params);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'password'
        ]);
        $response->assertJsonMissingValidationErrors([
            'first_name',
            'last_name',
            'email'
        ]);
    }

    public function testRegistrationDifferentPasswords()
    {
        $response = $this->register($this->getSuccessCredentials([
            'password_confirmation' => 'notSecret'
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'password'
        ]);
        $response->assertJsonMissingValidationErrors([
            'first_name',
            'last_name',
            'email'
        ]);
    }

    public function testRegistrationExistingUser()
    {
        /** @var User $user */
        factory(User::class)->create([
            'email' => static::SUCCESS_EMAIL
        ]);

        $response = $this->register($this->getSuccessCredentials());

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'email'
        ]);
        $response->assertJsonMissingValidationErrors([
            'first_name',
            'last_name',
            'password'
        ]);
    }

    public function testRegistrationSuccess()
    {
        $response = $this->register($this->getSuccessCredentials());

        $response->assertSuccessful();
        $data = $response->json('data');

        $this->assertNotNull($data);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('user', $data);

        $userData = $data['user'];

        /** @var User $user */
        $user = User::query()
            ->join('auth_tokens', 'users.id', '=', 'auth_tokens.user_id')
            ->where('token', '=', $data['token'])
            ->where('email', '=', static::SUCCESS_EMAIL)
            ->firstOrFail();

        $this->assertEquals(static::SUCCESS_FIRST_NAME, $user->first_name);
        $this->assertEquals(static::SUCCESS_LAST_NAME, $user->last_name);
        $this->assertEquals(static::SUCCESS_EMAIL, $user->email);
        $this->assertEquals($user->first_name, $userData['first_name']);
        $this->assertEquals($user->last_name, $userData['last_name']);
        $this->assertEquals($user->email, $userData['email']);
        $this->assertEquals($user->id, $userData['id']);
    }

    private function getSuccessCredentials(array $overrides = []): array
    {
        return array_merge([
            'first_name' => static::SUCCESS_FIRST_NAME,
            'last_name' => static::SUCCESS_LAST_NAME,
            'email' => static::SUCCESS_EMAIL,
            'password' => static::SUCCESS_PASSWORD,
            'password_confirmation' => static::SUCCESS_PASSWORD
        ], $overrides);
    }

    private function register(array $params = []): TestResponse
    {
        return $this->postJson(route('api.register'), $params);
    }
}