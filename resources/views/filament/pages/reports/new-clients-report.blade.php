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

    {{ $this->content }}
</x-filament-panels::page>
