<x-filament-panels::page>
    {{-- Date Filter --}}
    <x-filament::section heading="Filters">
        <div style="display: flex; gap: 1.25rem; flex-wrap: wrap; align-items: flex-end;">
            <div>
                <div style="font-size: 13px; font-weight: 500; margin-bottom: 6px;">Date</div>
                <input type="date"
                       wire:model.live="filterDate"
                       class="fi-input"
                       style="padding: 6px 12px;" />
            </div>
        </div>
    </x-filament::section>

    {{-- Daily Summary --}}
    @php $s = $this->getDailySummary(); @endphp
    <x-filament::section heading="Daily Summary">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1.5rem;">
            <div>
                <div style="font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.55;">Loans Disbursed</div>
                <div style="font-size: 28px; font-weight: 700; margin-top: 4px;">{{ $s['count'] }}</div>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.55;">Total Loan Amount</div>
                <div style="font-size: 28px; font-weight: 700; margin-top: 4px;">{{ number_format($s['total']) }}</div>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.55;">Total Interest</div>
                <div style="font-size: 28px; font-weight: 700; margin-top: 4px;">{{ number_format($s['interest']) }}</div>
            </div>
        </div>
    </x-filament::section>

    {{ $this->content }}
</x-filament-panels::page>
