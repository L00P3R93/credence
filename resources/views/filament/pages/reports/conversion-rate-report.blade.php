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

    @php $data = $this->getConversionData(); @endphp
    <x-filament::section :heading="'Lead Conversion — ' . $this->getPeriodLabel()">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    @foreach(['Agent', 'Leads Created', 'Converted', 'Conversion Rate'] as $i => $col)
                        <th style="padding: 8px 16px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.55; border-bottom: 1px solid rgba(128,128,128,0.2); text-align: {{ $i === 0 ? 'left' : 'right' }};">
                            {{ $col }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr style="border-bottom: 1px solid rgba(128,128,128,0.1);">
                        <td style="padding: 10px 16px; font-weight: 500;">{{ $row['agent'] }}</td>
                        <td style="padding: 10px 16px; text-align: right; opacity: 0.8;">{{ $row['created'] }}</td>
                        <td style="padding: 10px 16px; text-align: right; opacity: 0.8;">{{ $row['converted'] }}</td>
                        <td style="padding: 10px 16px; text-align: right;">
                            @if ($row['rate'] !== null)
                                @php $rate = $row['rate']; @endphp
                                <x-filament::badge :color="$rate >= 50 ? 'success' : ($rate >= 20 ? 'warning' : 'danger')">
                                    {{ $rate }}%
                                </x-filament::badge>
                            @else
                                <span style="opacity: 0.4;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding: 2.5rem 16px; text-align: center; opacity: 0.5; font-size: 14px;">
                            No lead activity recorded for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-filament::section>
</x-filament-panels::page>
