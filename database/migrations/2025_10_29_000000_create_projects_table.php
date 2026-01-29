<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->foreignId('owner_id')->constrained('property_owners', 'owner_id')->cascadeOnDelete();
            $table->string('project_title', 200);
            $table->text('project_description');
            $table->text('project_location');
            $table->decimal('budget_range_min', 15, 2)->nullable();
            $table->decimal('budget_range_max', 15, 2)->nullable();
            $table->integer('lot_size');
            $table->enum('property_type', ['Residential', 'Commercial', 'Industrial', 'Agricultural']);
            $table->foreignId('type_id')->constrained('contractor_types', 'type_id')->cascadeOnDelete();
            $table->integer('to_finish')->nullable();
            $table->enum('project_status', ['open', 'bidding_closed', 'in_progress', 'completed', 'terminated'])->default('open');
            $table->foreignId('selected_contractor_id')->nullable()->constrained('contractors', 'contractor_id');
            $table->timestamp('bidding_deadline');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};


