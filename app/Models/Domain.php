<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    protected $fillable = [
        'name', 'slug', 'site_url', 'gsc_property', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function queries(): HasMany
    {
        return $this->hasMany(Query::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function dailySearchAnalytics(): HasMany
    {
        return $this->hasMany(DailySearchAnalytic::class);
    }

    public function dailyDomainSummaries(): HasMany
    {
        return $this->hasMany(DailyDomainSummary::class);
    }

    public function dailyQuerySummaries(): HasMany
    {
        return $this->hasMany(DailyQuerySummary::class);
    }

    public function dailyPageSummaries(): HasMany
    {
        return $this->hasMany(DailyPageSummary::class);
    }

    public function seoActions(): HasMany
    {
        return $this->hasMany(SeoAction::class);
    }

    public function syncRuns(): HasMany
    {
        return $this->hasMany(SyncRun::class);
    }
}
