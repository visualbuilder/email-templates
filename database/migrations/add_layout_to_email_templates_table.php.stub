<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('filament-email-templates.table_name');

        if (! Schema::hasColumn($table, 'layout')) {
            Schema::table($table, function (Blueprint $table) {
                $table->json('layout')->nullable()->after('content');
            });
        }
    }

    public function down(): void
    {
        $table = config('filament-email-templates.table_name');

        if (Schema::hasColumn($table, 'layout')) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('layout');
            });
        }
    }
};
