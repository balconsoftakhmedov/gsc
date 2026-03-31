<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyPageSummary extends Model
{
    protected $fillable = [
        'domain_id', 'page_id', 'stat_date', 'total_clicks',
        'total_impressions', 'avg_ctr', 'avg_position', 'query_count'
    ];

    protected $casts = [
        'stat_date' => 'date',
        'total_clicks' => 'integer',
        'total_impressions' => 'integer',
        'avg_ctr' => 'decimal:4',
        'avg_position' => 'decimal:4',
        'query_count' => 'integer',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
