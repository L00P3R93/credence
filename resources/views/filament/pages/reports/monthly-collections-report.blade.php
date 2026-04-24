<x-filament-panels::page>
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

    {{-- Collections Summary Table --}}
    @php $summary = $this->getCollectionsSummary(); @endphp
    <x-filament::section :heading="'Collection Summary — ' . \Carbon\Carbon::create($filterYear, $filterMonth)->format('F Y')">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    @foreach(['Metric', 'Expected', 'Collected', 'Percentage'] as $i => $col)
                        <th style="padding: 8px 16px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.55; border-bottom: 1px solid rgba(128,128,128,0.2); text-align: {{ $i === 0 ? 'left' : 'right' }};">
                            {{ $col }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach([
                    ['label' => 'Loan Amount (Principal)', 'key' => 'loan_amount'],
                    ['label' => 'Loan Interest',           'key' => 'loan_interest'],
                    ['label' => 'Total',                   'key' => 'total'],
                ] as $row)
                    @php
                        $d = $summary[$row['key']];
                        $isTotal = $row['key'] === 'total';
                        $pctStr = $d['percentage'];
                        $pctNum = is_numeric(str_replace('%', '', $pctStr)) ? (float) str_replace('%', '', $pctStr) : null;
                    @endphp
                    <tr style="border-bottom: 1px solid rgba(128,128,128,0.1); {{ $isTotal ? 'font-weight: 600;' : '' }}">
                        <td style="padding: 11px 16px;">{{ $row['label'] }}</td>
                        <td style="padding: 11px 16px; text-align: right; font-family: monospace;">{{ number_format($d['expected'], 2) }}</td>
                        <td style="padding: 11px 16px; text-align: right; font-family: monospace;">{{ number_format($d['collected'], 2) }}</td>
                        <td style="padding: 11px 16px; text-align: right;">
                            @if ($pctNum !== null)
                                <x-filament::badge :color="$pctNum >= 90 ? 'success' : ($pctNum >= 50 ? 'warning' : 'danger')">
                                    {{ $pctStr }}
                                </x-filament::badge>
                            @else
                                <span style="opacity: 0.4;">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::section>

    {{-- Detailed Loan Table --}}
    {{ $this->content }}
</x-filament-panels::page>
