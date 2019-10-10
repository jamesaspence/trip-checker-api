<?php

use Faker\Generator as Faker;

$factory->define(App\Models\TemplateItem::class, function (Faker $faker) {
    return [
        'item' => $faker->word,
        'order' => $faker->numberBetween(1)
    ];
});
