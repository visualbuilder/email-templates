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
        Schema::create(config('email-templates.theme_table_name'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191)->nullable()->comment('Name');
            $table->json('colours')->nullable();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('email-templates.theme_table_name'));
    }
};
