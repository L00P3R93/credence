<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Lead::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Customer::class)->nullable()->constrained()->cascadeOnDelete();
            $table->text('work_address')->nullable();
            $table->text('home_address')->nullable();
            $table->foreignIdFor(\App\Models\Town::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('work_map_link')->nullable();
            $table->string('home_map_link')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Ensure either lead or customer is provided
        // Add check constraint using raw SQL
        DB::statement('ALTER TABLE addresses ADD CONSTRAINT check_lead_or_customer CHECK ( (lead_id IS NOT NULL) + (customer_id IS NOT NULL) = 1 )');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
