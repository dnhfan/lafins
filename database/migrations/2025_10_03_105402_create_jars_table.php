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
        Schema::create('jars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // mỗi user có 6 hũ riêng
            $table->string('name'); // tên hũ (NEC, FFA, EDU, LTSS, PLAY, GIVE)
            $table->decimal('percentage', 5, 2)->default(0); // % phân bổ
            $table->decimal('balance', 15, 2)->default(0); // số dư hiện tại
            $table->timestamps();
            
            // Đảm bảo mỗi user chỉ có 1 jar với mỗi tên
            $table->unique(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jars');
    }
};