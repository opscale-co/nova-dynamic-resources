<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_resources_fields', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('template_id');
            $table->string('type');
            $table->string('label');
            $table->string('name');
            $table->boolean('required')->default(false);
            $table->boolean('display_in_index')->default(true);
            $table->json('rules')->nullable();
            $table->json('config')->nullable();
            $table->json('hooks')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('template_id')
                ->references('id')
                ->on('dynamic_resources_templates')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index('template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_resources_fields');
    }
};
