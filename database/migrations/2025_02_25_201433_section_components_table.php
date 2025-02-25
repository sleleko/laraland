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
        Schema::create('section_components', function (Blueprint $table) {
            $table->id()->unique();
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->string('type'); // hero, text, image, features, cta, etc.
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable(); // Гибкие настройки для компонента
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_components');
    }
};
