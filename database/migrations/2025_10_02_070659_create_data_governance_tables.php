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
        Schema::create('data_domains', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('data_categories')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('data_residency_zones', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('region');
            $table->json('country_codes');
            $table->string('default_controller')->nullable();
            $table->json('approved_services')->nullable();
            $table->unsignedTinyInteger('risk_rating')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('data_assets', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('data_domain_id')->nullable()->constrained('data_domains');
            $table->foreignId('steward_id')->nullable()->constrained('users');
            $table->string('key')->unique();
            $table->string('name');
            $table->string('classification');
            $table->string('processing_purpose');
            $table->json('data_elements');
            $table->json('lawful_bases');
            $table->unsignedInteger('retention_period_days')->nullable();
            $table->boolean('requires_dpia')->default(false);
            $table->json('residency_exceptions')->nullable();
            $table->json('monitoring_controls')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['classification']);
            $table->index(['requires_dpia']);
            $table->index(['next_review_at']);
        });

        Schema::create('data_residency_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_asset_id')->constrained('data_assets')->cascadeOnDelete();
            $table->foreignId('data_residency_zone_id')->constrained('data_residency_zones')->cascadeOnDelete();
            $table->enum('storage_role', ['primary', 'replica', 'processing']);
            $table->string('lawful_basis');
            $table->string('encryption_profile');
            $table->string('data_controller');
            $table->boolean('cross_border_allowed')->default(false);
            $table->json('transfer_safeguards')->nullable();
            $table->json('audit_controls')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['data_asset_id', 'data_residency_zone_id', 'storage_role'], 'asset_zone_role_unique');
        });

        Schema::create('dpia_records', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('data_asset_id')->constrained('data_assets')->cascadeOnDelete();
            $table->enum('status', ['draft', 'in_review', 'mitigation_required', 'approved', 'rejected'])->default('draft');
            $table->string('risk_level')->default('low');
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->text('assessment_summary');
            $table->json('mitigation_actions')->nullable();
            $table->json('residual_risks')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('next_review_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['risk_score']);
        });

        Schema::create('dpia_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpia_record_id')->constrained('dpia_records')->cascadeOnDelete();
            $table->string('category');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->text('finding');
            $table->text('recommendation')->nullable();
            $table->enum('status', ['open', 'in_progress', 'mitigated'])->default('open');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('mitigated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dpia_findings');
        Schema::dropIfExists('dpia_records');
        Schema::dropIfExists('data_residency_policies');
        Schema::dropIfExists('data_assets');
        Schema::dropIfExists('data_residency_zones');
        Schema::dropIfExists('data_domains');
    }
};
