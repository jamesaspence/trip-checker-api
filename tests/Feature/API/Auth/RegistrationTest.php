<?php


namespace Tests\Feature\API\Auth;


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Laracore\Repository\ModelRepository;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    const SUCCESS_NAME = 'Test Name';
    const SUCCESS_EMAIL = 'success@pinda.test';
    const SUCCESS_PASSWORD = 'secret';

    public function testRegistrationNoCredentials()
    {
        $response = $this->register();

        $response->assertJsonValidationErrors([
            'name',
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
            'name',
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
            'name',
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
            'name',
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
            'name',
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
        $this->assertCount(1, $data);

        $repository = app(ModelRepository::class);
        $repository->setModel(User::class);

        /** @var User $user */
        $user = $repository->query()
            ->join('auth_tokens', 'users.id', '=', 'auth_tokens.user_id')
            ->where('token', '=', $data['token'])
            ->where('email', '=', static::SUCCESS_EMAIL)
            ->firstOrFail();

        $this->assertEquals(static::SUCCESS_NAME, $user->name);
        $this->assertEquals(static::SUCCESS_EMAIL, $user->email);
    }

    private function getSuccessCredentials(array $overrides = []): array
    {
        return array_merge([
            'name' => static::SUCCESS_NAME,
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