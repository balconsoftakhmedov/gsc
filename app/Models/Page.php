<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'domain_id', 'url', 'normalized_url', 'path', 'page_type', 'first_seen_at', 'last_seen_at'
    ];

    protected $casts = [
        'first_seen_at' => 'date',
        'last_seen_at' => 'date',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function dailySearchAnalytics(): HasMany
    {
        return $this->hasMany(DailySearchAnalytic::class);
    }

    public function dailyPageSummaries(): HasMany
    {
        return $this->hasMany(DailyPageSummary::class);
    }

    public function seoActions(): HasMany
    {
        return $this->hasMany(SeoAction::class);
    }
}
