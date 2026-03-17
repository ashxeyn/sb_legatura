<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `user_activity_logs` MODIFY `activity_type` VARCHAR(60) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `user_activity_logs` MODIFY `activity_type` ENUM(
            'user_registered',
            'failed_login_attempt',
            'project_reported',
            'profile_updated',
            'password_reset',
            'email_verified',
            'account_status_changed'
        ) NOT NULL");
    }
};
