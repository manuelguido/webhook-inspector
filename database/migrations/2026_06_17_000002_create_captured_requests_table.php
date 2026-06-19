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
        Schema::create('captured_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('webhook_bin_id')->constrained()->cascadeOnDelete();
            $table->string('method', 12);
            $table->string('path', 2048);
            $table->string('full_url', 2048);
            $table->json('headers');
            $table->json('query_params');
            $table->text('raw_body')->nullable();
            $table->json('json_body')->nullable();
            $table->string('content_type')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedInteger('body_size')->default(0);
            $table->boolean('body_truncated')->default(false);
            $table->timestamp('captured_at')->index();
            $table->timestamps();

            $table->index(['webhook_bin_id', 'captured_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captured_requests');
    }
};
