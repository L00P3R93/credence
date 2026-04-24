<x-filament-panels::page>
    {{-- Period Filter --}}
    <x-filament::section heading="Filters">
        <div style="display: flex; gap: 1.25rem; flex-wrap: wrap; align-items: flex-end;">
            <div>
                <div style="font-size: 13px; font-weight: 500; margin-bottom: 6px;">Month</div>
                <x-filament::input.select wire:model.live="filterMonth">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </x-filament::input.select>
            </div>
            <div>
                <div style="font-size: 13px; font-weight: 500; margin-bottom: 6px;">Year</div>
                <x-filament::input.select wire:model.live="filterYear">
                    @foreach(range(\Carbon\Carbon::now()->year - 3, \Carbon\Carbon::now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </x-filament::input.select>
            </div>
        </div>
    </x-filament::section>

    {{-- Summary --}}
    @php $s = $this->getBookSummary(); @endphp
    <x-filament::section heading="Book Summary">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1.5rem;">
            @foreach ([
                ['label' => 'New Loans',  'count' => $s['new_loans']['count'],  'total' => $s['new_loans']['total']],
                ['label' => 'Old Loans',  'count' => $s['old_loans']['count'],  'total' => $s['old_loans']['total']],
                ['label' => 'Refinanced', 'count' => $s['top_ups']['count'],    'total' => $s['top_ups']['total']],
                ['label' => 'Rolled',     'count' => $s['rolled']['count'],     'total' => $s['rolled']['total']],
            ] as $stat)
                <div>
                    <div style="font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.55;">{{ $stat['label'] }}</div>
                    <div style="font-size: 22px; font-weight: 700; margin-top: 4px;">{{ number_format($stat['total']) }}</div>
                    <div style="font-size: 12px; opacity: 0.55; margin-top: 2px;">{{ $stat['count'] }} {{ Str::plural('loan', $stat['count']) }}</div>
                </div>
            @endforeach
            <div style="border-left: 2px solid currentColor; opacity: 0.15; height: 100%;"></div>
            <div>
                <div style="font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.55;">Book Total</div>
                <div style="font-size: 22px; font-weight: 700; margin-top: 4px;">{{ number_format($s['book_total']['total']) }}</div>
                <div style="font-size: 12px; opacity: 0.55; margin-top: 2px;">
                    Interest: {{ number_format($s['book_total']['interest']) }}
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- Table --}}
    {{ $this->content }}
</x-filament-panels::page>
