<?php


namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\AuthToken;
use App\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    /**
     * @var AuthTokenGenerator
     */
    private $generator;

    /**
     * CredentialController constructor.
     */
    public function __construct(AuthTokenGenerator $generator)
    {
        $this->generator = $generator;
    }


    /**
     * Registers a user via the API.
     *
     * @route /api/v1/register
     * @method POST
     * @param Request $request
     * @return \App\Models\AuthToken|\Illuminate\Http\Response
     */
    public function registerUser(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = \Hash::make($request->password);
        $user->save();

        $authToken = $this->generator->generateNewAuthToken($user);

        return response()
            ->json([
                'status' => 'success',
                'message' => 'Successfully registered user',
                'data' => [
                    'user' => $user,
                    'token' => $authToken->token
                ]
            ], 201);
    }

    /**
     * Logs in a user via the API.
     *
     * @route /api/v1/login
     * @method POST
     * @param Request $request
     * @param AuthManager $auth
     * @return \App\Models\AuthToken|\Illuminate\Http\Response
     */
    public function login(Request $request, AuthManager $auth)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!$auth->guard()->once(['email' => $request->email, 'password' => $request->password])) {
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'Login error',
                    'errors' => [
                        'email' => [
                            'Email or password is incorrect.'
                        ]
                    ]
                ], 401);
        }

        $authToken = $this->generator->generateNewAuthToken($request->user());

        return response()
            ->json([
                'status' => 'success',
                'message' => 'Successfully logged in',
                'data' => [
                    'user' => $request->user(),
                    'token' => $authToken->token
                ]
            ], 201);
    }

    public function logout(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->getAuthToken()->delete();

        return response(null, 204);
    }

}