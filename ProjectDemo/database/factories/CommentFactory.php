<?php

use Faker\Generator as Faker;

$factory->define(App\comment::class, function (Faker $faker) {
    return [
        'comment' => $faker->sentence(),
        'id_blog' => rand(1,10),
        'id_user' => rand(1,10),
        'id_category' => rand(1,10),
    ];
});
