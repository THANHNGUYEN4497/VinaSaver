<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
class CategorySeederTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Schema::hasTable('categories')) {
            DB::table('categories')->insert([
                [ 'category' => 'laravel','created_at' => now(),'updated_at' => now()],
                [ 'category' => 'java','created_at' => now(),'updated_at' => now()],
                [ 'category' => 'javascript','created_at' => now(),'updated_at' => now()],
                [ 'category' => 'c#','created_at' => now(),'updated_at' => now()],
                [ 'category' => 'html','created_at' => now(),'updated_at' => now()],
                [ 'category' => 'css','created_at' => now(),'updated_at' => now()],
                [ 'category' => 'boostrap','created_at' => now(),'updated_at' => now()],
                [ 'category' => 'angular','created_at' => now(),'updated_at' => now()],
                [ 'category' => 'vuejs','created_at' => now(),'updated_at' => now()],
                [ 'category' => 'ReactJs','created_at' => now(),'updated_at' => now()]

            ]);
        }
    }
}
