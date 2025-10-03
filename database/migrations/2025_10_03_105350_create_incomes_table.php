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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // mỗi income thuộc về 1 user
            $table->decimal('amount', 15, 2); // số tiền
            $table->string('source')->nullable(); // nguồn thu nhập (lương, thưởng…)
            $table->text('description')->nullable();
            $table->date('date')->default(now()); // ngày nhận
            $table->timestamps();
            
            // Index cho query hiệu quả hơn
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};