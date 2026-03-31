<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $templatesTable = config('filament-email-templates.table_name', 'vb_email_templates');
        $themesTable = config('filament-email-templates.theme_table_name', 'vb_email_templates_themes');

        Schema::table($templatesTable, function (Blueprint $table) use ($templatesTable) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
            // Drop the original unique index on (key, language) to allow
            // the same key+language for different tenants and global templates
            $table->dropUnique("{$templatesTable}_key_language_unique");
        });

        Schema::table($themesTable, function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        // handled by table drops
    }
};
