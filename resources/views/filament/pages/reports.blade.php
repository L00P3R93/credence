<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 2rem;">
        @foreach ($this->getReportGroups() as $group)
            <x-filament::section :heading="$group['label']">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
                    @foreach ($group['reports'] as $report)
                        <a href="{{ $report['url'] }}" style="display: block; text-decoration: none;">
                            <x-filament::section>
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                    <x-dynamic-component :component="$report['icon']" style="width: 20px; height: 20px; flex-shrink: 0;" />
                                    <strong style="font-size: 14px; font-weight: 600;">{{ $report['label'] }}</strong>
                                </div>
                                <p style="font-size: 13px; margin: 0; opacity: 0.65; line-height: 1.4;">{{ $report['description'] }}</p>
                            </x-filament::section>
                        </a>
                    @endforeach
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
