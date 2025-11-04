<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_fields', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('resource_id');
            $table->string('type');
            $table->string('label');
            $table->string('name');
            $table->boolean('required')->default(false);
            $table->json('rules')->nullable();
            $table->json('config')->nullable();
            $table->json('hooks')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('resource_id')
                ->references('id')
                ->on('dynamic_resources')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Indexes for better performance
            $table->unique(['resource_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_fields');
    }
};
