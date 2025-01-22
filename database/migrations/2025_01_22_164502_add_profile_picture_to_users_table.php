<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfilePictureToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * This method adds the 'profile_picture' column to the 'users' table.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Adds a nullable string column for storing the profile picture path
            $table->string('profile_picture')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method removes the 'profile_picture' column from the 'users' table.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drops the 'profile_picture' column if it exists
            $table->dropColumn('profile_picture');
        });
    }
}