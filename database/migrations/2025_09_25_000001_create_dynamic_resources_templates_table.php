<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_resources_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('base_class')->nullable();
            $table->string('singular_label');
            $table->string('label');
            $table->string('uri_key');
            $table->string('title')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uri_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_resources_templates');
    }
};
