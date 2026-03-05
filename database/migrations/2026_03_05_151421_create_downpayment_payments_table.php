<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('downpayment_payments', function (Blueprint $table) {
            $table->increments('dp_payment_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('owner_id');
            $table->unsignedInteger('contractor_user_id');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_type', ['cash', 'check', 'bank_transfer', 'online_payment']);
            $table->string('transaction_number', 100)->nullable();
            $table->string('receipt_photo', 255)->nullable();
            $table->date('transaction_date')->nullable();
            $table->enum('payment_status', ['submitted', 'approved', 'rejected', 'deleted'])->default('submitted');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->datetime('updated_at')->nullable();

            $table->index('project_id', 'idx_dp_project');
            $table->index('owner_id', 'idx_dp_owner');
            $table->index('payment_status', 'idx_dp_status');
        });

        // Migrate existing downpayment records from milestone_payments (item_id = -1)
        $existing = \Illuminate\Support\Facades\DB::table('milestone_payments')
            ->where('item_id', -1)
            ->get();

        foreach ($existing as $row) {
            \Illuminate\Support\Facades\DB::table('downpayment_payments')->insert([
                'project_id'         => $row->project_id,
                'owner_id'           => $row->owner_id,
                'contractor_user_id' => $row->contractor_user_id,
                'amount'             => $row->amount,
                'payment_type'       => $row->payment_type,
                'transaction_number' => $row->transaction_number,
                'receipt_photo'      => $row->receipt_photo,
                'transaction_date'   => $row->transaction_date,
                'payment_status'     => $row->payment_status,
                'reason'             => $row->reason,
                'updated_at'         => $row->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downpayment_payments');
    }
};
