<x-filament-panels::page>

<style>
    .ec-timeline { max-width: 860px; }

    /* Month heading */
    .ec-month {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 2rem 0 1rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgb(var(--gray-400));
    }
    .ec-month::after {
        content: '';
        flex: 1;
        height: 1px;
        background: rgb(var(--gray-200));
    }
    .dark .ec-month { color: rgb(var(--gray-500)); }
    .dark .ec-month::after { background: rgb(var(--gray-700)); }

    /* Event card */
    .ec-card {
        display: flex;
        gap: 1.25rem;
        align-items: flex-start;
        padding: 1rem 1.25rem;
        border-radius: 0.625rem;
        background: white;
        border: 1px solid rgb(var(--gray-200));
        margin-bottom: 0.625rem;
        transition: box-shadow 0.2s, border-color 0.2s;
        text-decoration: none;
        color: inherit;
    }
    .ec-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.07);
        border-color: rgb(var(--primary-300));
    }
    .dark .ec-card {
        background: rgb(var(--gray-800));
        border-color: rgb(var(--gray-700));
    }
    .dark .ec-card:hover { border-color: rgb(var(--primary-500)); }

    /* Past event card */
    .ec-card.ec-past {
        opacity: 0.55;
        background: rgb(var(--gray-50));
    }
    .dark .ec-card.ec-past { background: rgb(var(--gray-900)); }

    /* Today marker */
    .ec-today-marker {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin: 1.5rem 0 1rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: rgb(var(--primary-600));
    }
    .ec-today-marker::before,
    .ec-today-marker::after {
        content: '';
        flex: 1;
        height: 2px;
        background: rgb(var(--primary-400));
        border-radius: 1px;
    }

    /* Date badge */
    .ec-date {
        flex-shrink: 0;
        width: 52px;
        text-align: center;
        line-height: 1;
    }
    .ec-date-day {
        font-size: 2rem;
        font-weight: 700;
        color: rgb(var(--gray-900));
        line-height: 1;
        display: block;
    }
    .dark .ec-date-day { color: rgb(var(--gray-100)); }
    .ec-date-weekday {
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgb(var(--gray-400));
        display: block;
        margin-top: 0.15rem;
    }
    .ec-date-month {
        font-size: 0.65rem;
        color: rgb(var(--gray-400));
        display: block;
        margin-top: 0.1rem;
    }

    /* Today highlight on date */
    .ec-card.ec-is-today .ec-date-day {
        color: rgb(var(--primary-600));
    }
    .ec-card.ec-is-today .ec-date-weekday {
        color: rgb(var(--primary-500));
    }

    /* Divider */
    .ec-divider {
        width: 1px;
        align-self: stretch;
        background: rgb(var(--gray-150));
        flex-shrink: 0;
        margin: 0.1rem 0;
    }
    .dark .ec-divider { background: rgb(var(--gray-700)); }

    /* Content */
    .ec-body { flex: 1; min-width: 0; }
    .ec-customer {
        font-size: 0.95rem;
        font-weight: 600;
        color: rgb(var(--gray-900));
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0.25rem;
    }
    .dark .ec-customer { color: rgb(var(--gray-100)); }
    .ec-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1rem;
        font-size: 0.78rem;
        color: rgb(var(--gray-500));
        align-items: center;
    }
    .ec-meta-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .ec-meta-item svg { flex-shrink: 0; }

    /* Status badge */
    .ec-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.55rem;
        border-radius: 20px;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.04em;
    }
    .ec-badge-draft     { background: rgb(var(--gray-100));  color: rgb(var(--gray-600)); }
    .ec-badge-sent      { background: #dbeafe; color: #1e40af; }
    .ec-badge-accepted  { background: #d1fae5; color: #065f46; }
    .ec-badge-completed { background: #ede9fe; color: #5b21b6; }
    .ec-badge-rejected  { background: #fee2e2; color: #991b1b; }
    .ec-badge-expired   { background: #fef3c7; color: #92400e; }
    .dark .ec-badge-draft { background: rgb(var(--gray-700)); color: rgb(var(--gray-300)); }

    /* Right arrow */
    .ec-arrow {
        color: rgb(var(--gray-300));
        flex-shrink: 0;
        align-self: center;
        transition: color 0.2s, transform 0.2s;
    }
    .ec-card:hover .ec-arrow {
        color: rgb(var(--primary-500));
        transform: translateX(3px);
    }

    /* Filter bar */
    .ec-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 1.5rem;
    }
    .ec-filter-btn {
        padding: 0.35rem 0.9rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 500;
        border: 1px solid rgb(var(--gray-200));
        background: white;
        color: rgb(var(--gray-600));
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
        line-height: 1.4;
    }
    .ec-filter-btn:hover {
        background: rgb(var(--gray-50));
        border-color: rgb(var(--gray-300));
    }
    .ec-filter-btn.active {
        background: rgb(var(--primary-500));
        border-color: rgb(var(--primary-500));
        color: white;
    }
    .dark .ec-filter-btn {
        background: rgb(var(--gray-800));
        border-color: rgb(var(--gray-700));
        color: rgb(var(--gray-300));
    }
    .dark .ec-filter-btn:hover { background: rgb(var(--gray-700)); }
    .dark .ec-filter-btn.active {
        background: rgb(var(--primary-500));
        border-color: rgb(var(--primary-500));
        color: white;
    }
    .ec-filter-sep {
        width: 1px;
        height: 20px;
        background: rgb(var(--gray-200));
        margin: 0 0.25rem;
    }
    .dark .ec-filter-sep { background: rgb(var(--gray-700)); }
    .ec-date-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .ec-date-input {
        padding: 0.35rem 2rem 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 500;
        border: 1px solid rgb(var(--gray-200));
        background: white;
        color: rgb(var(--gray-700));
        cursor: pointer;
        line-height: 1.4;
        outline: none;
        transition: border-color 0.15s;
    }
    .ec-date-input:focus { border-color: rgb(var(--primary-400)); }
    .ec-date-input.has-value {
        border-color: rgb(var(--primary-400));
        background: rgb(var(--primary-50));
        color: rgb(var(--primary-700));
        padding-right: 2.25rem;
    }
    .dark .ec-date-input {
        background: rgb(var(--gray-800));
        border-color: rgb(var(--gray-700));
        color: rgb(var(--gray-300));
    }
    .dark .ec-date-input.has-value {
        background: rgb(var(--primary-900));
        color: rgb(var(--primary-200));
    }
    .ec-date-clear {
        position: absolute;
        right: 0.55rem;
        background: none;
        border: none;
        cursor: pointer;
        color: rgb(var(--primary-400));
        padding: 0;
        line-height: 1;
        display: flex;
        align-items: center;
    }
    .ec-date-clear:hover { color: rgb(var(--primary-600)); }

    /* Empty state */
    .ec-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: rgb(var(--gray-400));
        font-size: 0.9rem;
    }

    /* Past section toggle */
    .ec-past-heading {
        cursor: pointer;
        user-select: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: rgb(var(--gray-500));
        background: rgb(var(--gray-100));
        border: 1px solid rgb(var(--gray-200));
        margin: 1.5rem 0 0;
        transition: background 0.2s;
    }
    .ec-past-heading:hover { background: rgb(var(--gray-150)); }
    .dark .ec-past-heading {
        background: rgb(var(--gray-800));
        border-color: rgb(var(--gray-700));
        color: rgb(var(--gray-400));
    }
</style>

<div class="ec-timeline">

    {{-- ── Status filter bar ── --}}
    @php
        $statuses = [
            null       => 'All',
            'draft'    => 'Draft',
            'sent'     => 'Sent',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'expired'  => 'Expired',
        ];
    @endphp
    <div class="ec-filters">
        @foreach($statuses as $value => $label)
            <button
                class="ec-filter-btn {{ $statusFilter === $value ? 'active' : '' }}"
                wire:click="setFilter({{ $value === null ? 'null' : "'$value'" }})"
            >{{ $label }}</button>
        @endforeach

        <div class="ec-filter-sep"></div>

        <div class="ec-date-input-wrap">
            <input
                type="month"
                class="ec-date-input {{ $monthFilter ? 'has-value' : '' }}"
                wire:model.live="monthFilter"
                title="Filter by month"
            >
            @if($monthFilter)
                <button class="ec-date-clear" wire:click="clearMonthFilter" title="Clear month">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>

        <div class="ec-date-input-wrap">
            <input
                type="date"
                class="ec-date-input {{ $dateFilter ? 'has-value' : '' }}"
                wire:model.live="dateFilter"
                title="Filter by specific date"
            >
            @if($dateFilter)
                <button class="ec-date-clear" wire:click="clearDateFilter" title="Clear date">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- ── Upcoming events ── --}}
    @if($upcoming->isEmpty())
        <div class="ec-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" style="margin: 0 auto 0.75rem; display:block; opacity:0.4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
            No upcoming events scheduled.
        </div>
    @else
        @foreach($upcoming as $monthKey => $events)
            @php $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $monthKey); @endphp
            <div class="ec-month">{{ $monthDate->format('F Y') }}</div>

            @foreach($events as $quote)
                @php
                    $isToday = $quote->event_date->isToday();
                    $editUrl = \App\Filament\Resources\QuoteResource::getUrl('edit', ['record' => $quote]);
                    $customer = $quote->customer?->full_name ?? trim(($quote->customer_first_name ?? '') . ' ' . ($quote->customer_last_name ?? ''));
                    $customer = $customer ?: ($quote->customer_company ?? $quote->customer?->company_name ?? '—');
                @endphp

                @if($isToday)
                    <div class="ec-today-marker">Today</div>
                @endif

                <a href="{{ $editUrl }}" class="ec-card{{ $isToday ? ' ec-is-today' : '' }}">
                    {{-- Date badge --}}
                    <div class="ec-date">
                        <span class="ec-date-day">{{ $quote->event_date->format('d') }}</span>
                        <span class="ec-date-weekday">{{ $quote->event_date->format('D') }}</span>
                        <span class="ec-date-month">{{ $quote->event_date->format('M') }}</span>
                    </div>

                    <div class="ec-divider"></div>

                    {{-- Main content --}}
                    <div class="ec-body">
                        <div class="ec-customer">{{ $customer }}</div>
                        <div class="ec-meta">
                            @php $companyName = $quote->customer?->company_name ?? $quote->customer_company ?? null; @endphp
                            @if($companyName)
                                <span class="ec-meta-item">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                                    {{ $companyName }}
                                </span>
                            @endif
                            @if($quote->event_start_time && $quote->event_end_time)
                                <span class="ec-meta-item">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                                    {{ \Carbon\Carbon::parse($quote->event_start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($quote->event_end_time)->format('H:i') }}
                                </span>
                            @endif
                            @if($quote->attendee_count)
                                <span class="ec-meta-item">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                    {{ number_format($quote->attendee_count) }} guests
                                </span>
                            @endif
                            @if($quote->event_type)
                                <span class="ec-meta-item">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                                    {{ $quote->event_type }}
                                </span>
                            @endif
                            <span class="ec-badge ec-badge-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span>
                            <span class="ec-meta-item" style="margin-left: auto; font-size: 0.72rem; opacity: 0.6;">
                                {{ $quote->quote_number }}
                            </span>
                        </div>
                    </div>

                    {{-- Arrow --}}
                    <svg class="ec-arrow" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            @endforeach
        @endforeach
    @endif

    {{-- ── Past events (collapsible) ── --}}
    @if($past->isNotEmpty())
        <div x-data="{ open: false }">
            <button class="ec-past-heading" @click="open = !open">
                <svg x-show="!open" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                <svg x-show="open" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                Past events ({{ $past->flatten()->count() }})
            </button>

            <div x-show="open" x-transition style="margin-top: 1rem;">
                @foreach($past as $monthKey => $events)
                    @php $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $monthKey); @endphp
                    <div class="ec-month">{{ $monthDate->format('F Y') }}</div>

                    @foreach($events as $quote)
                        @php
                            $editUrl  = \App\Filament\Resources\QuoteResource::getUrl('edit', ['record' => $quote]);
                            $customer = $quote->customer?->full_name ?? trim(($quote->customer_first_name ?? '') . ' ' . ($quote->customer_last_name ?? ''));
                            $customer = $customer ?: ($quote->customer_company ?? $quote->customer?->company_name ?? '—');
                        @endphp
                        <a href="{{ $editUrl }}" class="ec-card ec-past">
                            <div class="ec-date">
                                <span class="ec-date-day">{{ $quote->event_date->format('d') }}</span>
                                <span class="ec-date-weekday">{{ $quote->event_date->format('D') }}</span>
                                <span class="ec-date-month">{{ $quote->event_date->format('M') }}</span>
                            </div>
                            <div class="ec-divider"></div>
                            <div class="ec-body">
                                <div class="ec-customer">{{ $customer }}</div>
                                <div class="ec-meta">
                                    @php $companyName = $quote->customer?->company_name ?? $quote->customer_company ?? null; @endphp
                                    @if($companyName)
                                        <span class="ec-meta-item">
                                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                                            {{ $companyName }}
                                        </span>
                                    @endif
                                    @if($quote->attendee_count)
                                        <span class="ec-meta-item">
                                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                            {{ number_format($quote->attendee_count) }} guests
                                        </span>
                                    @endif
                                    @if($quote->event_type)
                                        <span class="ec-meta-item">{{ $quote->event_type }}</span>
                                    @endif
                                    <span class="ec-badge ec-badge-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span>
                                    <span class="ec-meta-item" style="margin-left: auto; font-size: 0.72rem; opacity: 0.6;">{{ $quote->quote_number }}</span>
                                </div>
                            </div>
                            <svg class="ec-arrow" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

</div>

</x-filament-panels::page>
