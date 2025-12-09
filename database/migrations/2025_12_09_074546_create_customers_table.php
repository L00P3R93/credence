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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Lead::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('id_no')->unique()->index();
            $table->string('phone')->unique()->index();
            $table->string('phone_alt')->nullable();
            $table->string('gender')->default('m');
            $table->date('dob')->nullable();
            $table->string('work_email')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('status')->default('active');
            $table->decimal('loan_limit', 10, 2)->default(0);
            $table->text('comments')->nullable();
            $table->text('collection_comments')->nullable();
            $table->boolean('has_loan')->default(false);
            $table->boolean('has_cheques')->default(false);
            $table->foreignIdFor(\App\Models\User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Bank::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\BankBranch::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('converted_to_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
