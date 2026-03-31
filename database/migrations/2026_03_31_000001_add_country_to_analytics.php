<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_search_analytics', function (Blueprint $table) {
            $table->string('country', 3)->nullable()->after('page_id');
            // Update unique index to include country
            $table->dropUnique('d_q_p_date_unique');
            $table->unique(['domain_id', 'query_id', 'page_id', 'stat_date', 'country'], 'd_q_p_date_c_unique');
        });

        Schema::create('daily_country_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->string('country', 3);
            $table->date('stat_date')->index();
            $table->integer('total_clicks');
            $table->integer('total_impressions');
            $table->decimal('avg_ctr', 8, 4);
            $table->decimal('avg_position', 8, 4);
            $table->timestamps();

            $table->unique(['domain_id', 'country', 'stat_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_country_summaries');
        Schema::table('daily_search_analytics', function (Blueprint $table) {
            $table->dropUnique('d_q_p_date_c_unique');
            $table->unique(['domain_id', 'query_id', 'page_id', 'stat_date'], 'd_q_p_date_unique');
            $table->dropColumn('country');
        });
    }
};
