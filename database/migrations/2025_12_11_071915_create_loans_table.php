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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Customer::class)->constrained()->cascadeOnDelete();
            $table->date('given_date')->default(now());
            $table->date('due_date');
            $table->decimal('loan_amount', 10, 2)->default(0.00);
            $table->decimal('loan_interest', 10, 2)->default(0.00);
            $table->decimal('processing_fee', 10, 2)->default(0.00);
            $table->decimal('loan_total', 10, 2)->default(0.00);
            $table->integer('loan_period')->default(1);
            $table->boolean('new_loan')->default(false);
            $table->boolean('old_loan')->default(false);
            $table->boolean('top_up')->default(false);
            $table->foreignId('agent')->constrained('users')->cascadeOnDelete();
            $table->foreignId('temp_agent')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending_verification'); // pending_verification, pending_confirmation, pending_approval, pending_disbursement, disbursed, overdue, past_overdue, canceled, written_off, fraud, deleted
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('collection_agent')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('collection_officer')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Bank::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\BankBranch::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
