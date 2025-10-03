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
        Schema::create('outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // mỗi outcome thuộc về 1 user
            $table->foreignId('jar_id')->nullable()->constrained('jars')->onDelete('set null'); // chi tiêu từ hũ nào
            $table->decimal('amount', 15, 2);
            $table->string('category')->nullable(); // ăn uống, đi chơi…
            $table->text('description')->nullable();
            $table->date('date')->default(now());
            $table->timestamps();
            
            // Index cho query hiệu quả hơn
            $table->index(['user_id', 'date']);
            $table->index(['jar_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outcomes');
    }
};