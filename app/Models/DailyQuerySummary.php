<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyQuerySummary extends Model
{
    protected $fillable = [
        'domain_id', 'query_id', 'stat_date', 'total_clicks',
        'total_impressions', 'avg_ctr', 'avg_position', 'page_count'
    ];

    protected $casts = [
        'stat_date' => 'date',
        'total_clicks' => 'integer',
        'total_impressions' => 'integer',
        'avg_ctr' => 'decimal:4',
        'avg_position' => 'decimal:4',
        'page_count' => 'integer',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function query(): BelongsTo
    {
        return $this->belongsTo(Query::class);
    }
}
