<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Domain;
use App\Models\DailyDomainSummary;
use Carbon\Carbon;

class DashboardOverview extends Component
{
    public $selectedDomainId;
    public $lookbackDays = 30;

    public function mount()
    {
        $this->selectedDomainId = Domain::where('is_active', true)->first()->id ?? null;
    }

    public function render()
    {
        $domains = Domain::where('is_active', true)->get();

        $startDate = Carbon::now()->subDays($this->lookbackDays)->format('Y-m-d');

        $summaries = DailyDomainSummary::where('domain_id', $this->selectedDomainId)
            ->where('stat_date', '>=', $startDate)
            ->orderBy('stat_date', 'asc')
            ->get();

        $latestSummary = $summaries->last();
        $previousSummary = $summaries->count() > 1 ? $summaries[$summaries->count() - 2] : null;

        $dates = $summaries->pluck('stat_date')->map(fn($d) => $d->format('M d'))->toArray();
        $clicks = $summaries->pluck('total_clicks')->toArray();
        $impressions = $summaries->pluck('total_impressions')->toArray();
        $ctrs = $summaries->pluck('avg_ctr')->map(fn($v) => (float)$v)->toArray();
        $positions = $summaries->pluck('avg_position')->map(fn($v) => (float)$v)->toArray();

        $this->dispatch('charts-updated', chartData: [
            'dates' => $dates,
            'clicks' => $clicks,
            'impressions' => $impressions,
            'ctrs' => $ctrs,
            'positions' => $positions,
        ]);

        return view('livewire.dashboard-overview', [
            'domains' => $domains,
            'latestSummary' => $latestSummary,
            'previousSummary' => $previousSummary,
            'chartData' => [
                'dates' => $dates,
                'clicks' => $clicks,
                'impressions' => $impressions,
                'ctrs' => $ctrs,
                'positions' => $positions,
            ]
        ]);
    }
}
