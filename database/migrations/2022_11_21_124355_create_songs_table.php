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
        Schema::create('songs', function (Blueprint $table) {
            $table->id();

            $table->text('name')->nullable();
            $table->bigInteger('singer_id')->nullable();
            $table->bigInteger('writer_id')->nullable();
            $table->bigInteger('category_id')->nullable();
            $table->boolean('has_male')->nullable();
            $table->boolean('has_female')->nullable();

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
        Schema::dropIfExists('songs');
    }
};
