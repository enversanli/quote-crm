<x-filament-panels::page>

    {{-- Chart.js loaded synchronously before Alpine x-init runs --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <style>
        /* ── Layout ────────────────────────────────────────────────── */
        .st-year-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .st-year-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgb(var(--gray-400));
        }
        .st-year-btn {
            padding: 0.25rem 1rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 9999px;
            border: 1px solid rgb(var(--gray-300));
            background: white;
            color: rgb(var(--gray-700));
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s, color 0.15s;
        }
        .dark .st-year-btn {
            background: rgb(var(--gray-800));
            color: rgb(var(--gray-300));
            border-color: rgb(var(--gray-600));
        }
        .st-year-btn:hover { border-color: rgb(var(--primary-400)); }
        .st-year-btn.active {
            background: rgb(var(--primary-500));
            color: white;
            border-color: rgb(var(--primary-500));
        }

        /* ── KPI grid ──────────────────────────────────────────────── */
        .st-kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 1024px) {
            .st-kpi-grid { grid-template-columns: repeat(5, 1fr); }
        }
        .st-kpi-card {
            background: white;
            border: 1px solid rgb(var(--gray-200));
            border-radius: 0.75rem;
            padding: 1.25rem;
        }
        .dark .st-kpi-card {
            background: rgb(var(--gray-800));
            border-color: rgb(var(--gray-700));
        }
        .st-kpi-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgb(var(--gray-400));
            margin-bottom: 0.25rem;
        }
        .st-kpi-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: rgb(var(--gray-900));
            line-height: 1.2;
        }
        .dark .st-kpi-value { color: white; }
        .st-kpi-value.amber { color: rgb(var(--primary-600)); }
        .st-kpi-value.green { color: #059669; }
        .st-kpi-sub {
            font-size: 0.7rem;
            color: rgb(var(--gray-400));
            margin-top: 0.2rem;
        }

        /* ── Panel cards ────────────────────────────────────────────── */
        .st-panel {
            background: white;
            border: 1px solid rgb(var(--gray-200));
            border-radius: 0.75rem;
            padding: 1.25rem;
        }
        .dark .st-panel {
            background: rgb(var(--gray-800));
            border-color: rgb(var(--gray-700));
        }
        .st-panel-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: rgb(var(--gray-700));
            margin-bottom: 1rem;
        }
        .dark .st-panel-title { color: rgb(var(--gray-300)); }
        .st-panel-sub {
            font-size: 0.7rem;
            color: rgb(var(--gray-400));
            margin-top: -0.6rem;
            margin-bottom: 0.75rem;
        }

        /* ── Two-column rows ──────────────────────────────────────── */
        .st-row-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 1024px) {
            .st-row-2 { grid-template-columns: 1fr 1fr; }
        }

        /* ── Top customers table ───────────────────────────────────── */
        .st-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
        .st-table th {
            text-align: left;
            padding: 0.4rem 0.5rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgb(var(--gray-400));
            border-bottom: 1px solid rgb(var(--gray-100));
        }
        .dark .st-table th { border-color: rgb(var(--gray-700)); }
        .st-table th.right, .st-table td.right { text-align: right; }
        .st-table th.center, .st-table td.center { text-align: center; }
        .st-table td {
            padding: 0.625rem 0.5rem;
            color: rgb(var(--gray-700));
            border-bottom: 1px solid rgb(var(--gray-50));
        }
        .dark .st-table td {
            color: rgb(var(--gray-200));
            border-color: rgba(var(--gray-700), 0.5);
        }
        .st-table tr:hover td { background: rgb(var(--gray-50)); }
        .dark .st-table tr:hover td { background: rgba(var(--gray-700), 0.3); }
        .st-rank {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            background: rgb(var(--gray-100));
            color: rgb(var(--gray-500));
        }
        .dark .st-rank { background: rgb(var(--gray-700)); color: rgb(var(--gray-400)); }
        .st-rank.gold {
            background: rgba(var(--primary-100), 1);
            color: rgb(var(--primary-700));
        }
        .st-td-name { font-weight: 500; color: rgb(var(--gray-800)); }
        .dark .st-td-name { color: rgb(var(--gray-200)); }
        .st-td-revenue { font-weight: 600; color: rgb(var(--gray-900)); }
        .dark .st-td-revenue { color: white; }

        /* ── Organizer split ──────────────────────────────────────── */
        .st-org-split {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }
        .st-org-item { flex: 1; text-align: center; }
        .st-org-pct {
            font-size: 1.5rem;
            font-weight: 700;
            color: rgb(var(--gray-900));
        }
        .dark .st-org-pct { color: white; }
        .st-org-type {
            font-size: 0.7rem;
            text-transform: capitalize;
            color: rgb(var(--gray-500));
        }
        .st-org-count {
            font-size: 0.65rem;
            color: rgb(var(--gray-400));
        }

        /* ── Empty state ──────────────────────────────────────────── */
        .st-empty {
            font-size: 0.875rem;
            color: rgb(var(--gray-400));
            text-align: center;
            padding: 2rem 0;
        }
    </style>

    {{-- ── Year filter ────────────────────────────────────────────── --}}
    <div class="st-year-bar">
        <span class="st-year-label">Year</span>
        @foreach($this->getAvailableYears() as $y)
            <button
                wire:click="setYear({{ $y }})"
                class="st-year-btn {{ $year == $y ? 'active' : '' }}"
            >{{ $y }}</button>
        @endforeach
    </div>

    {{-- ── KPI cards ──────────────────────────────────────────────── --}}
    <div class="st-kpi-grid">

        <div class="st-kpi-card">
            <div class="st-kpi-label">Total Revenue</div>
            <div class="st-kpi-value">€&nbsp;{{ number_format($totalRevenue, 2, ',', '.') }}</div>
            <div class="st-kpi-sub">accepted &amp; completed</div>
        </div>

        <div class="st-kpi-card">
            <div class="st-kpi-label">Total Events</div>
            <div class="st-kpi-value">{{ $totalEvents }}</div>
            <div class="st-kpi-sub">accepted &amp; completed</div>
        </div>

        <div class="st-kpi-card">
            <div class="st-kpi-label">Avg. per Event</div>
            <div class="st-kpi-value">€&nbsp;{{ number_format($avgRevenue, 2, ',', '.') }}</div>
            <div class="st-kpi-sub">average revenue</div>
        </div>

        <div class="st-kpi-card">
            <div class="st-kpi-label">Conversion Rate</div>
            <div class="st-kpi-value {{ $conversionRate >= 30 ? 'green' : '' }}">{{ $conversionRate }}&nbsp;%</div>
            <div class="st-kpi-sub">{{ $acceptedCount }} / {{ $totalQuotes }} quotes</div>
        </div>

        <div class="st-kpi-card">
            <div class="st-kpi-label">Open Pipeline</div>
            <div class="st-kpi-value amber">€&nbsp;{{ number_format($pipelineValue, 2, ',', '.') }}</div>
            <div class="st-kpi-sub">{{ $pipelineCount }} open quote{{ $pipelineCount === 1 ? '' : 's' }}</div>
        </div>

    </div>

    {{-- ── Monthly Revenue Chart ────────────────────────────────────── --}}
    <div class="st-panel" style="margin-bottom:1.5rem;"
         x-data="{
             chart: null,
             init() {
                 const labels  = {{ Js::from($monthLabels) }};
                 const revenue = {{ Js::from($monthlyRevenue) }};
                 const events  = {{ Js::from($monthlyEvents) }};
                 const isDark  = document.documentElement.classList.contains('dark');
                 const gridC   = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
                 const textC   = isDark ? '#9ca3af' : '#6b7280';

                 if (this.chart) this.chart.destroy();
                 this.chart = new Chart(this.$refs.canvas, {
                     type: 'bar',
                     data: {
                         labels,
                         datasets: [
                             {
                                 label: 'Revenue (€)',
                                 data: revenue,
                                 backgroundColor: 'rgba(245,158,11,0.18)',
                                 borderColor: 'rgba(245,158,11,0.85)',
                                 borderWidth: 2,
                                 borderRadius: 5,
                                 yAxisID: 'y',
                             },
                             {
                                 label: 'Events',
                                 data: events,
                                 type: 'line',
                                 borderColor: 'rgba(59,130,246,0.75)',
                                 backgroundColor: 'transparent',
                                 pointBackgroundColor: 'rgba(59,130,246,0.9)',
                                 borderWidth: 2,
                                 pointRadius: 4,
                                 tension: 0.35,
                                 yAxisID: 'y2',
                             },
                         ],
                     },
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         interaction: { mode: 'index', intersect: false },
                         plugins: {
                             legend: { position: 'top', labels: { color: textC, boxWidth: 12, font: { size: 12 } } },
                             tooltip: {
                                 callbacks: {
                                     label: (ctx) => ctx.datasetIndex === 0
                                         ? ' € ' + Number(ctx.parsed.y).toLocaleString('de-DE', { minimumFractionDigits: 2 })
                                         : ' ' + ctx.parsed.y + ' event(s)',
                                 },
                             },
                         },
                         scales: {
                             y:  { beginAtZero: true, position: 'left',  grid: { color: gridC }, ticks: { color: textC, callback: (v) => '€ ' + v.toLocaleString('de-DE') } },
                             y2: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { color: textC, stepSize: 1, precision: 0 } },
                             x:  { grid: { display: false }, ticks: { color: textC } },
                         },
                     },
                 });
             },
         }"
    >
        <div class="st-panel-title">Monthly Revenue &amp; Events — {{ $year }}</div>
        <div style="position:relative; height:260px;">
            <canvas x-ref="canvas"></canvas>
        </div>
    </div>

    {{-- ── Status funnel + Event types ──────────────────────────────── --}}
    <div class="st-row-2">

        <div class="st-panel"
             x-data="{
                 chart: null,
                 init() {
                     const order  = {{ Js::from($statusOrder) }};
                     const counts = {{ Js::from($statusCounts) }};
                     const colors = {{ Js::from($statusColors) }};
                     const labels = order.filter(s => counts[s] > 0);
                     const isDark = document.documentElement.classList.contains('dark');
                     const textC  = isDark ? '#d1d5db' : '#374151';

                     if (this.chart) this.chart.destroy();
                     this.chart = new Chart(this.$refs.canvas, {
                         type: 'doughnut',
                         data: {
                             labels,
                             datasets: [{
                                 data: labels.map(s => counts[s]),
                                 backgroundColor: labels.map(s => colors[s]),
                                 borderWidth: 2,
                                 borderColor: isDark ? '#1f2937' : '#ffffff',
                             }],
                         },
                         options: {
                             responsive: true,
                             maintainAspectRatio: false,
                             cutout: '62%',
                             plugins: {
                                 legend: { position: 'right', labels: { color: textC, font: { size: 12 }, padding: 12, boxWidth: 12 } },
                             },
                         },
                     });
                 },
             }"
        >
            <div class="st-panel-title">Quote Status — {{ $year }}</div>
            <div style="position:relative; height:220px;">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        <div class="st-panel"
             x-data="{
                 chart: null,
                 init() {
                     const raw     = {{ Js::from($eventTypes) }};
                     const labels  = Object.keys(raw);
                     const data    = Object.values(raw);
                     const palette = ['#f59e0b','#3b82f6','#10b981','#8b5cf6','#ef4444','#06b6d4'];
                     const isDark  = document.documentElement.classList.contains('dark');
                     const gridC   = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
                     const textC   = isDark ? '#9ca3af' : '#6b7280';

                     if (this.chart) this.chart.destroy();
                     this.chart = new Chart(this.$refs.canvas, {
                         type: 'bar',
                         data: {
                             labels,
                             datasets: [{
                                 data,
                                 backgroundColor: labels.map((_, i) => palette[i % palette.length] + '33'),
                                 borderColor:     labels.map((_, i) => palette[i % palette.length]),
                                 borderWidth: 2,
                                 borderRadius: 5,
                             }],
                         },
                         options: {
                             indexAxis: 'y',
                             responsive: true,
                             maintainAspectRatio: false,
                             plugins: { legend: { display: false } },
                             scales: {
                                 x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, color: textC }, grid: { color: gridC } },
                                 y: { ticks: { color: textC }, grid: { display: false } },
                             },
                         },
                     });
                 },
             }"
        >
            <div class="st-panel-title">Event Types — {{ $year }}</div>
            <div style="position:relative; height:220px;">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

    </div>

    {{-- ── Organizer split + Top customers ─────────────────────────── --}}
    <div class="st-row-2">

        <div class="st-panel"
             x-data="{
                 chart: null,
                 init() {
                     const raw    = {{ Js::from($organizerTypes) }};
                     const labels = Object.keys(raw);
                     const isDark = document.documentElement.classList.contains('dark');
                     const textC  = isDark ? '#d1d5db' : '#374151';

                     if (this.chart) this.chart.destroy();
                     this.chart = new Chart(this.$refs.canvas, {
                         type: 'doughnut',
                         data: {
                             labels,
                             datasets: [{
                                 data: Object.values(raw),
                                 backgroundColor: ['rgba(59,130,246,0.75)', 'rgba(245,158,11,0.75)'],
                                 borderWidth: 2,
                                 borderColor: isDark ? '#1f2937' : '#ffffff',
                             }],
                         },
                         options: {
                             responsive: true,
                             maintainAspectRatio: false,
                             cutout: '60%',
                             plugins: {
                                 legend: { position: 'bottom', labels: { color: textC, font: { size: 12 }, padding: 16, boxWidth: 12 } },
                                 tooltip: {
                                     callbacks: {
                                         label: (ctx) => {
                                             const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                             const pct   = total ? Math.round(ctx.parsed / total * 100) : 0;
                                             return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                                         },
                                     },
                                 },
                             },
                         },
                     });
                 },
             }"
        >
            <div class="st-panel-title">Organizer Type — {{ $year }}</div>
            <div class="st-panel-sub">{{ $totalOrganizers }} quotes with organizer data</div>
            @if ($totalOrganizers === 0)
                <p class="st-empty">No organizer data for {{ $year }}</p>
            @else
                <div class="st-org-split">
                    @foreach ($organizerTypes as $type => $count)
                        @php $pct = $totalOrganizers > 0 ? round(($count / $totalOrganizers) * 100) : 0 @endphp
                        <div class="st-org-item">
                            <div class="st-org-pct">{{ $pct }}&nbsp;%</div>
                            <div class="st-org-type">{{ $type }}</div>
                            <div class="st-org-count">({{ $count }})</div>
                        </div>
                    @endforeach
                </div>
                <div style="position:relative; height:160px;">
                    <canvas x-ref="canvas"></canvas>
                </div>
            @endif
        </div>

        <div class="st-panel">
            <div class="st-panel-title">Top Customers by Revenue — {{ $year }}</div>
            @if ($topCustomers->isEmpty())
                <p class="st-empty">No accepted events in {{ $year }}</p>
            @else
                <table class="st-table">
                    <thead>
                        <tr>
                            <th style="width:2rem;">#</th>
                            <th>Customer</th>
                            <th class="center">Events</th>
                            <th class="right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topCustomers as $i => $c)
                            <tr>
                                <td>
                                    <span class="st-rank {{ $i === 0 ? 'gold' : '' }}">{{ $i + 1 }}</span>
                                </td>
                                <td class="st-td-name">{{ $c['name'] }}</td>
                                <td class="center" style="color:rgb(var(--gray-500))">{{ $c['events'] }}</td>
                                <td class="right st-td-revenue">€&nbsp;{{ number_format($c['revenue'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>

</x-filament-panels::page>
