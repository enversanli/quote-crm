@php use App\Filament\Resources\QuoteResource; @endphp

<div>
    @if ($clashes->isNotEmpty())
        <x-filament::dropdown placement="bottom-end" width="md" max-height="28rem">
            <x-slot name="trigger">
                <x-filament::icon-button
                    icon="heroicon-o-exclamation-triangle"
                    color="danger"
                    :badge="$quoteCount"
                    badge-color="danger"
                    label="Events on the same day"
                    :tooltip="$quoteCount . ' quotes share a day with another event'"
                />
            </x-slot>

            <x-filament::dropdown.header icon="heroicon-o-calendar-days" color="danger">
                Events on the same day
            </x-filament::dropdown.header>

            @foreach ($clashes as $group)
                @php
                    $first          = $group->first();
                    $acceptedCount  = $group->whereIn('status', ['accepted', 'completed'])->count();
                @endphp

                <x-filament::dropdown.list>
                    <div style="padding: 0.375rem 0.5rem; font-size: 0.75rem; font-weight: 600; display: flex; justify-content: space-between; gap: 0.5rem;">
                        <span>{{ $first->event_date->format('D, d.m.Y') }} · {{ $first->business?->name ?? 'No venue' }}</span>
                        <span style="color: {{ $acceptedCount > 1 ? '#dc2626' : '#d97706' }};">
                            {{ $acceptedCount > 1 ? 'Double booking' : 'Check availability' }}
                        </span>
                    </div>

                    @foreach ($group as $quote)
                        <x-filament::dropdown.list.item
                            tag="a"
                            :href="QuoteResource::getUrl('edit', ['record' => $quote])"
                        >
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; width: 100%;">
                                <div style="min-width: 0;">
                                    <div>
                                        <strong>{{ $quote->quote_number }}</strong>
                                        · {{ QuoteResource::timeRange($quote->event_start_time, $quote->event_end_time) ?? 'no time set' }}
                                    </div>
                                    <div style="font-size: 0.75rem; opacity: 0.7; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $quote->customer_display_name }}
                                    </div>
                                </div>
                                <x-filament::badge :color="QuoteResource::statusColor($quote->status)">
                                    {{ ucfirst($quote->status) }}
                                </x-filament::badge>
                            </div>
                        </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            @endforeach
        </x-filament::dropdown>
    @endif
</div>
