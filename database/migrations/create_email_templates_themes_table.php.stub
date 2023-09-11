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
        Schema::create(config('filament-email-templates.theme_table_name'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191)->nullable()->comment('Name');
            $table->json('colours')->nullable();
            $table->boolean('is_default')->default(0)->comment('1: Active | 0: Not Active');
            $table->softDeletes();
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
        Schema::dropIfExists(config('filament-email-templates.theme_table_name'));
    }
};
