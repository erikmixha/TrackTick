<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->string('username')->unique();
            $table->string('password');
            $table->boolean('active')->default(0); 
            $table->boolean('deleted')->default(0); 
            $table->timestamps();
        });

        // Insert static user data
        DB::table('users')->insert([
            [
                'provider_id' => 1,
                'username' => 'usernameProvider1',
                'password' => bcrypt('provider1'),
                'active' => 0,
                'deleted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'provider_id' => 2,
                'username' => 'usernameProvider2',
                'password' => bcrypt('provider2'), 
                'active' => 0,
                'deleted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
