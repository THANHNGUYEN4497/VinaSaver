<?php

use Faker\Generator as Faker;

$factory->define(App\blog::class, function (Faker $faker) {
    return [
        'title'  => $faker->title,
        'content' => $faker->paragraph(),
        'id_category' => rand(1,10),
        'created_at' => now(),
        'updated_at' => now(),
    ];
});
