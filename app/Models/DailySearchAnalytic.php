<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySearchAnalytic extends Model
{
    protected $fillable = [
        'domain_id', 'query_id', 'page_id', 'stat_date',
        'clicks', 'impressions', 'ctr', 'position'
    ];

    protected $casts = [
        'stat_date' => 'date:Y-m-d',
        'clicks' => 'integer',
        'impressions' => 'integer',
        'ctr' => 'decimal:4',
        'position' => 'decimal:4',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function seoQuery(): BelongsTo
    {
        return $this->belongsTo(Query::class, 'query_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
