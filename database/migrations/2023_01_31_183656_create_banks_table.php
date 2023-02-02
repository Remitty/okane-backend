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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->enum('type', ['ach', 'wire'])->default('ach');
            $table->char('relation_id', 255);
            $table->char('routing_number', 255);
            $table->char('account_number', 255);
            $table->char('owner_name', 255);
            $table->char('nickname', 255)->nullable();
            $table->char('status', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banks');
    }
};
