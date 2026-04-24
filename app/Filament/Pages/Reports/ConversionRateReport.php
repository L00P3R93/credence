<?php

namespace App\Filament\Pages\Reports;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ConversionRateReport extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Conversion Rate Report';

    protected string $view = 'filament.pages.reports.conversion-rate-report';

    public int $filterMonth;
    public int $filterYear;

    public function mount(): void
    {
        $this->filterMonth = Carbon::now()->month;
        $this->filterYear = Carbon::now()->year;
    }

    public function getConversionData(): Collection
    {
        $start = Carbon::create($this->filterYear, $this->filterMonth)->startOfMonth();
        $end = Carbon::create($this->filterYear, $this->filterMonth)->endOfMonth();

        $createdInPeriod = Lead::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $convertedInPeriod = Lead::query()
            ->where('status', LeadStatus::CONVERTED)
            ->whereBetween('converted_at', [$start, $end])
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $agentIds = $createdInPeriod->keys()
            ->merge($convertedInPeriod->keys())
            ->unique();

        $users = User::whereIn('id', $agentIds)->get()->keyBy('id');

        return $agentIds->map(function (int $userId) use ($createdInPeriod, $convertedInPeriod, $users): array {
            $created   = (int) ($createdInPeriod[$userId] ?? 0);
            $converted = (int) ($convertedInPeriod[$userId] ?? 0);

            return [
                'agent'        => $users[$userId]->name ?? 'Unknown',
                'created'      => $created,
                'converted'    => $converted,
                'rate'         => $created > 0 ? round($converted / $created * 100, 1) : null,
            ];
        })->sortByDesc('converted')->values();
    }

    public function getPeriodLabel(): string
    {
        return Carbon::create($this->filterYear, $this->filterMonth)->format('F Y');
    }
}
