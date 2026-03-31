<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Query extends Model
{
    protected $fillable = [
        'domain_id', 'query', 'normalized_query', 'is_branded', 'tag_type', 'first_seen_at', 'last_seen_at'
    ];

    protected $casts = [
        'is_branded' => 'boolean',
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

    public function dailyQuerySummaries(): HasMany
    {
        return $this->hasMany(DailyQuerySummary::class);
    }

    public function seoActions(): HasMany
    {
        return $this->hasMany(SeoAction::class);
    }
}
