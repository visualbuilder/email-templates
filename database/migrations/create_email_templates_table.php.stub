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
      Schema::create(config('filament-email-templates.table_name'), function (Blueprint $table) {
                 $columnName = config('filament-email-templates.theme_table_name') . '_id';
                 $table->increments('id');
                 $table->unsignedInteger($columnName)->nullable();
                 $table->string('key', 191)->comment('Must be unique when combined with language');
                 $table->string('language', 8)->default(config('filament-email-templates.default_locale'),);
                 $table->string('name', 191)->nullable()->comment('Friendly Name');
                 $table->string('view', 191)->default(config('filament-email-templates.default_view'))->comment('Blade Template to load into');
                 $table->json('from')->nullable()->comment('From address to override system default');
                 $table->json('cc')->nullable();
                 $table->json('bcc')->nullable();
                 $table->string('subject', 191)->nullable();
                 $table->string('preheader', 191)->nullable()->comment('Only shows on some email clients below subject line');
                 $table->string('title', 50)->nullable()->comment('First line of email h1 string');
                 $table->text('content')->nullable();
                 $table->string('logo', 191)->nullable();
                 $table->timestamps();
                 $table->softDeletes();
                 $table->unique(['key', 'language']);
                 $table->foreign($columnName)->references('id')->on(config('filament-email-templates.theme_table_name'))->onDelete('set null');
             });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('filament-email-templates.table_name'));
    }
};
