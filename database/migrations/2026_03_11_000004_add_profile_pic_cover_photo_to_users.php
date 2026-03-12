<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_pic', 255)->nullable()->after('bio');
            $table->string('cover_photo', 255)->nullable()->after('profile_pic');
        });

        // Backfill from property_owners so existing profile images are preserved
        DB::statement("
            UPDATE users u
            INNER JOIN property_owners po ON po.user_id = u.user_id
            SET u.profile_pic = po.profile_pic,
                u.cover_photo = po.cover_photo
            WHERE u.profile_pic IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_pic', 'cover_photo']);
        });
    }
};
