<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncRun extends Model
{
    protected $fillable = [
        'domain_id', 'target_date', 'started_at', 'finished_at',
        'status', 'rows_fetched', 'rows_inserted', 'rows_updated', 'error_message'
    ];

    protected $casts = [
        'target_date' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'rows_fetched' => 'integer',
        'rows_inserted' => 'integer',
        'rows_updated' => 'integer',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
