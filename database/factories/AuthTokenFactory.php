<?php

use App\Http\Controllers\API\AuthTokenGenerator;
use Faker\Generator as Faker;

$factory->define(App\Models\AuthToken::class, function (Faker $faker) {
    /** @var AuthTokenGenerator $generator */
    $generator = app(AuthTokenGenerator::class);
    return [
        'token' => $generator->generateUniqueToken()
    ];
});