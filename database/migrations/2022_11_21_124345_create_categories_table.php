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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->text('name');

            $table->timestampTz('created_at', 6)->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->timestampTz('updated_at', 6)->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestampTz('deleted_at', 6)->nullable();
            $table->bigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
