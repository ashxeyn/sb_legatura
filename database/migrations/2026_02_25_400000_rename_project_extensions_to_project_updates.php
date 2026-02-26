<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Rename project_extensions table to project_updates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('project_extensions', 'project_updates');
    }

    public function down(): void
    {
        Schema::rename('project_updates', 'project_extensions');
    }
};
