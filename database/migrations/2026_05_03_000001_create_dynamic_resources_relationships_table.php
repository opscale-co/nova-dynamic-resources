<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_resources_relationships', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('template_id');
            $table->string('name');
            $table->string('label');
            $table->string('cardinality');
            $table->ulid('related_template_id');
            $table->string('foreign_key');
            $table->string('inverse_name')->nullable();
            $table->boolean('required')->default(false);
            $table->json('rules')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('template_id')
                ->references('id')
                ->on('dynamic_resources_templates')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('related_template_id')
                ->references('id')
                ->on('dynamic_resources_templates')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index('template_id');
            $table->index(['related_template_id', 'cardinality']);
            $table->unique(['template_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_resources_relationships');
    }
};
