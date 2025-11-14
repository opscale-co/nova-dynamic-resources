<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_actions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('resource_id');
            $table->string('class');
            $table->string('label');
            $table->json('config')->nullable();
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
            $table->index('resource_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_actions');
    }
};
