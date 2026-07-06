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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->text('summary')->nullable();
            $table->string('status')->default('draft'); // draft, pending, published, rejected
            $table->text('reject_reason')->nullable();
            $table->boolean('is_featured')->default(false);
            
            // Relational Fields
            $table->unsignedBigInteger('inputter_id');
            $table->unsignedBigInteger('authoriser_id')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            
            // Analytics Counter
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
