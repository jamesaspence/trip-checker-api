<?php


namespace Tests\Feature\API;


use App\Models\AuthToken;
use App\Models\User;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\TestResponse;

/**
 * @method MakesHttpRequests actingAs(User $user, $driver = null)
 * @see InteractsWithAuthentication::actingAs()
 * @method TestResponse json($method, $uri, array $data = [], array $headers = [])
 * @see MakesHttpRequests::json()
 */
trait MakesAuthenticatedRequest
{
    /**
     * Creates a test user and returns it.
     *
     * @param array $attributes
     * @return User
     */
    private function createUser(array $attributes = []): User
    {
        return factory(User::class)
            ->states('api')
            ->create($attributes);
    }

    /**
     * M
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @param User|null $user
     * @param array $headers
     * @return TestResponse
     */
    private function makeRequest(string $url, string $method, array $params = [], User $user = null, array $headers = []): TestResponse
    {
        if (is_null($user)) {
            return $this->json($method, $url, $params, $headers);
        }

        return $this->actingAs($user, 'api')
            ->json($method, $url, $params, $headers);
    }

}