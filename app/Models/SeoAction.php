<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoAction extends Model
{
    protected $fillable = [
        'domain_id', 'page_id', 'query_id', 'user_id',
        'action_type', 'action_note', 'action_date'
    ];

    protected $casts = [
        'action_date' => 'date',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function seoQuery(): BelongsTo
    {
        return $this->belongsTo(Query::class, 'query_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
