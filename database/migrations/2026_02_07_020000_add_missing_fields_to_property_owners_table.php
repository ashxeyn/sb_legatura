<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('property_owners', function (Blueprint $table) {
            // Add is_active column (the main missing field causing the error)
            $table->boolean('is_active')->default(0)->after('verification_status');
            
            // Add other missing columns for account management
            $table->date('suspension_until')->nullable()->after('is_active');
            $table->text('deletion_reason')->nullable()->after('suspension_until');
            $table->text('suspension_reason')->nullable()->after('deletion_reason');
            
            // Add missing valid_id_back_photo if it doesn't exist
            if (!Schema::hasColumn('property_owners', 'valid_id_back_photo')) {
                $table->string('valid_id_back_photo', 255)->nullable()->after('valid_id_photo');
            }
        });

        // Update verification_status enum to include 'approved' and 'deleted'
        DB::statement("ALTER TABLE property_owners MODIFY COLUMN verification_status ENUM('pending','rejected','approved','deleted') NOT NULL DEFAULT 'pending'");
        
        // Update any 'verified' status to 'approved' for consistency
        DB::table('property_owners')
            ->where('verification_status', 'verified')
            ->update(['verification_status' => 'approved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_owners', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'suspension_until',
                'deletion_reason',
                'suspension_reason',
            ]);
            
            if (Schema::hasColumn('property_owners', 'valid_id_back_photo')) {
                $table->dropColumn('valid_id_back_photo');
            }
        });

        DB::statement("ALTER TABLE property_owners MODIFY COLUMN verification_status ENUM('pending','rejected','verified','') NOT NULL DEFAULT 'pending'");
    }
};
