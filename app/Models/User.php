<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property string password
 * @property string last_name
 * @property string first_name
 * @property string email
 * @property integer id
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * @var AuthToken the current auth token used for this request.
     */
    private $authToken;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at', 'email_verified_at'
    ];

    /**
     * @return AuthToken
     */
    public function getAuthToken(): AuthToken
    {
        return $this->authToken;
    }

    /**
     * @param AuthToken $authToken
     */
    public function setAuthToken(AuthToken $authToken): void
    {
        $this->authToken = $authToken;
    }
}
