<?php


namespace App\Http\Controllers\API;


use App\Models\AuthToken;
use App\Models\User;

class AuthTokenGenerator
{
    public function generateUniqueToken(): string
    {
        do {
            $token = str_random(32);
        } while (AuthToken::query()->withTrashed()->where('token', '=', $token)->exists());

        return $token;
    }

    public function generateNewAuthToken(User $user): AuthToken
    {

        $authToken = new AuthToken();
        $authToken->token = $this->generateUniqueToken();
        $authToken->user()->associate($user);
        $authToken->save();

        $user->setAuthToken($authToken);

        return $authToken;
    }
}