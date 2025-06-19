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
        Schema::create('tests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->enum('type', ['full-length', 'daily-mock']);
                $table->boolean('is_free')->default(false);
                $table->integer('time_limit')->comment('in minutes');
                $table->integer('total_marks')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
