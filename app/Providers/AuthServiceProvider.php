<?php

namespace App\Providers;

use App\Models\AuthToken;
use Illuminate\Http\Request;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        \Auth::viaRequest('api-token', function (Request $request) {
            $apiToken = $request->headers->get('X-Auth-Token');

            /** @var AuthToken $authToken */
            $authToken = AuthToken::with('user')
                ->select('auth_tokens.*')
                ->join('users', 'auth_tokens.user_id', '=', 'users.id')
                ->where('token', '=', $apiToken)
                ->first();

            if (is_null($authToken)) {
                return null;
            }

            $user = $authToken->user;
            $user->setAuthToken($authToken);

            return $user;
        });
    }
}
