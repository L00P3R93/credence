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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('id_no')->unique()->index();
            $table->string('phone')->unique()->index();
            $table->string('phone_alt')->nullable();
            $table->string('gender')->default('m');
            $table->date('dob')->nullable();
            $table->string('work_email')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('status')->default('lead');
            $table->foreignIdFor(\App\Models\User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Bank::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\BankBranch::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
