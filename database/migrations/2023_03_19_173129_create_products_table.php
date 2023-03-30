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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->integer('price');
            $table->integer('current_price')->nullable();
            $table->integer('current_discount')->min(0)->max(99)->nullable();
            $table->text('description')->nullable();
            $table->date('exp_date');
            $table->text('photo');
            $table->integer('quantity')->default(1)->min(1);
            $table->foreignId('category_id')
            ->constrained('categories')
            ->cascadeOnDelete()
            ->cascadeOnUbdate();
            $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete()
            ->cascadeOnUbdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
