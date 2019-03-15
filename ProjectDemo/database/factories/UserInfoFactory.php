<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;


$factory->define(App\user::class, function (Faker $faker) {
    return [
        'fullname' => $faker->name,
        'username' => $faker->name.rand(1,10),
        'password' => Hash::make('123123'),
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'birthday' => $faker->datetime,
        'address' => $faker->country,
        'job' => $faker->jobTitle,
        'avatar' => 'null',
        'id_blog' => rand(1,10),
        'id_Comment' => rand(1,10),
        'remember_token' => Str::random(10),
    ];
});
