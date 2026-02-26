<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_extensions', function (Blueprint $table) {
            $table->increments('extension_id');

            // Project context
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('contractor_user_id')->comment('user_id of the submitting contractor');
            $table->unsignedInteger('owner_user_id')->comment('user_id of the property owner');

            // Snapshot of current end date at submission time (taken from last milestone end_date)
            $table->date('current_end_date')->comment('Project end date at time of request');
            $table->date('proposed_end_date')->comment('Requested new project end date');

            // Reason and additional cost
            $table->text('reason');
            $table->boolean('has_additional_cost')->default(false);
            $table->decimal('additional_amount', 12, 2)->nullable()->comment('Only set when has_additional_cost = true');
            $table->enum('allocation_method', ['new_milestone', 'redistribute'])
                ->nullable()
                ->comment('How extra cost is allocated when has_additional_cost = true');

            // Workflow status
            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])->default('pending');
            $table->text('owner_response')->nullable()->comment('Owner rejection reason or approval note');

            // Timestamps
            $table->timestamp('applied_at')->nullable()->comment('When the extension was actually applied to the project');
            $table->timestamps();

            // Indexes
            $table->index('project_id');
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_extensions');
    }
};
