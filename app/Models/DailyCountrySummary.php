<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCountrySummary extends Model
{
    protected $fillable = [
        'domain_id', 'country', 'stat_date', 'total_clicks',
        'total_impressions', 'avg_ctr', 'avg_position'
    ];

    protected $casts = [
        'stat_date' => 'date:Y-m-d',
        'total_clicks' => 'integer',
        'total_impressions' => 'integer',
        'avg_ctr' => 'decimal:4',
        'avg_position' => 'decimal:4',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
