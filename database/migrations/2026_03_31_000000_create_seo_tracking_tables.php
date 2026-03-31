<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('site_url');
            $table->string('gsc_property')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->text('query');
            $table->text('normalized_query');
            $table->boolean('is_branded')->default(false);
            $table->string('tag_type')->nullable();
            $table->date('first_seen_at')->nullable();
            $table->date('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['domain_id', 'normalized_query']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->text('url');
            $table->text('normalized_url');
            $table->string('path');
            $table->string('page_type')->nullable();
            $table->date('first_seen_at')->nullable();
            $table->date('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['domain_id', 'normalized_url']);
        });

        Schema::create('daily_search_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->foreignId('query_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->date('stat_date')->index();
            $table->integer('clicks');
            $table->integer('impressions');
            $table->decimal('ctr', 8, 4);
            $table->decimal('position', 8, 4);
            $table->timestamps();

            $table->unique(['domain_id', 'query_id', 'page_id', 'stat_date'], 'd_q_p_date_unique');
        });

        Schema::create('daily_domain_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->date('stat_date')->index();
            $table->integer('total_clicks');
            $table->integer('total_impressions');
            $table->decimal('avg_ctr', 8, 4);
            $table->decimal('avg_position', 8, 4);
            $table->integer('keyword_count');
            $table->integer('page_count');
            $table->timestamps();

            $table->unique(['domain_id', 'stat_date']);
        });

        Schema::create('daily_query_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->foreignId('query_id')->constrained()->onDelete('cascade');
            $table->date('stat_date')->index();
            $table->integer('total_clicks');
            $table->integer('total_impressions');
            $table->decimal('avg_ctr', 8, 4);
            $table->decimal('avg_position', 8, 4);
            $table->integer('page_count');
            $table->timestamps();

            $table->unique(['domain_id', 'query_id', 'stat_date'], 'd_q_date_unique');
        });

        Schema::create('daily_page_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->date('stat_date')->index();
            $table->integer('total_clicks');
            $table->integer('total_impressions');
            $table->decimal('avg_ctr', 8, 4);
            $table->decimal('avg_position', 8, 4);
            $table->integer('query_count');
            $table->timestamps();

            $table->unique(['domain_id', 'page_id', 'stat_date'], 'd_p_date_unique');
        });

        Schema::create('seo_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('query_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action_type');
            $table->text('action_note')->nullable();
            $table->date('action_date');
            $table->timestamps();
        });

        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->date('target_date');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->string('status')->default('pending');
            $table->integer('rows_fetched')->default(0);
            $table->integer('rows_inserted')->default(0);
            $table->integer('rows_updated')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
        Schema::dropIfExists('seo_actions');
        Schema::dropIfExists('daily_page_summaries');
        Schema::dropIfExists('daily_query_summaries');
        Schema::dropIfExists('daily_domain_summaries');
        Schema::dropIfExists('daily_search_analytics');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('queries');
        Schema::dropIfExists('domains');
    }
};
