<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('bundle_id')
                ->nullable()
                ->after('template_id')
                ->constrained('bundles')
                ->nullOnDelete();

            // Stable per-row identifier used by Nova's Repeater HasMany
            // preset to diff added/removed/updated rows across submissions.
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bundle_id');
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });

        Schema::dropIfExists('bundles');
    }
};
