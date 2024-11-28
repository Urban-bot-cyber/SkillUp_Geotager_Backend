<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // User who performed the action (nullable for guests)
            $table->string('action'); // Action type (click, scroll, input change)
            $table->string('component_type')->nullable(); // Component type (e.g., link, button, input type)
            $table->text('new_value')->nullable(); // New value (for user inputs)
            $table->string('url'); // URL where the action happened
            $table->timestamps(); // Created at and updated at timestamps
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_actions');
    }
};
