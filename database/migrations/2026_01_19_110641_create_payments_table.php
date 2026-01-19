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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Customer::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Loan::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable()->index();
            $table->decimal('amount', 10,2)->default(0.00);
            $table->string('payment_method')->default('m-pesa'); // m-pesa, bank_transfer, cash
            $table->string('receipt_no')->unique()->index();
            $table->dateTime('date_received')->default(now()->format('Y-m-d H:i:s'))->index();
            $table->string('status')->default('completed'); // suspended,completed, failed
            $table->foreignId('added_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('in_active')->default(false);
            $table->boolean('is_statement_fee')->default(false);
            $table->boolean('is_settlement')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
