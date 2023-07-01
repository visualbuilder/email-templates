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
        Schema::create(config('email-templates.table_name'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 191)->comment('Must be unique when combined with language');
            $table->string('language', 8)->default(config('email-templates.default_locale'),);
            $table->string('name', 191)->nullable()->comment('Friendly Name');
            $table->string('view', 191)->default(config('email-templates.default_view'))->comment('Blade Template to load into');
            $table->string('from', 191)->nullable()->comment('From address to override system default');
            $table->string('send_to',191)->nullable()->comment('The Notifiable model class');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject', 191)->nullable();
            $table->string('preheader', 191)->nullable()->comment('Only shows on some email clients below subject line');
            $table->string('title', 50)->nullable()->comment('First line of email h1 string');
            $table->text('content')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['key', 'language']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('email-templates.table_name'));
    }
};
