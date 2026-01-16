<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_resources_actions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('template_id');
            $table->string('class');
            $table->string('label');
            $table->json('config')->nullable();
            $table->json('metadata')->nullable();
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
        Schema::dropIfExists('dynamic_resources_actions');
    }
};
