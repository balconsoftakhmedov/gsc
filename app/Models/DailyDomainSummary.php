<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyDomainSummary extends Model
{
    protected $fillable = [
        'domain_id', 'stat_date', 'total_clicks', 'total_impressions',
        'avg_ctr', 'avg_position', 'keyword_count', 'page_count'
    ];

    protected $casts = [
        'stat_date' => 'date',
        'total_clicks' => 'integer',
        'total_impressions' => 'integer',
        'avg_ctr' => 'decimal:4',
        'avg_position' => 'decimal:4',
        'keyword_count' => 'integer',
        'page_count' => 'integer',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
