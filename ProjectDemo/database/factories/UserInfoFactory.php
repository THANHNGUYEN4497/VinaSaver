<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;


$factory->define(App\user::class, function (Faker $faker) {
    return [
        'fullname' => $faker->name,
        'username' => $faker->name . random(3),
        'password' => Hash::make('123123'),
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'birthday' => $faker->datetime,
        'address' => $faker->address,
        'job' => $faker->jobTitle,
        'avatar' => 'null',
        'id_blog' => random(10),
        'id_Comment' => random(10),
        'remmember_token' => Str::random(10),
    ];
});
