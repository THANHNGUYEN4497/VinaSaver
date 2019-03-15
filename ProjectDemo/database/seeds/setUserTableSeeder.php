<?php

use Illuminate\Database\Seeder;

class setUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\user::class,20)->create();
    }
}
