@php
    $roleLayout = auth()->user()->isAdmin() ? 'layouts.admin' : (auth()->user()->isInstructor() ? 'layouts.instructor' : 'layouts.staff');
    $labels = [
        'payment' => 'Payment',
        'attendance' => 'Attendance',
        'workout' => 'Workout Sessions',
        'member' => 'Members',
    ];
@endphp

@extends($roleLayout)

@section('title', 'Reports')

@if(auth()->user()->isAdmin())
    @section('page_title', 'Reports')
    @section('active_nav', 'reports')
@endif

@if(auth()->user()->isInstructor())
    @section('active', 'reports')
@endif

@section('content')
    <style>
        .report-shell {
            display: grid;
            gap: 22px;
        }
        .report-toolbar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: end;
        }
        .report-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 170px;
        }
        .report-value {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            color: var(--text);
            font: inherit;
        }
        .report-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }
        .report-stat {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
        }
        .report-stat::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 3px;
            background: var(--accent);
        }
        .report-stat.blue::before { background: var(--info); }
        .report-stat.green::before { background: var(--success); }
        .report-stat.orange::before { background: var(--warning); }
        .report-stat-label {
            color: var(--muted);
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }
        .report-stat-value {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.1;
        }
        .report-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }
        .report-chart {
            padding: 20px 20px 12px;
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--border);
        }
        .chart-bars {
            display: flex;
            align-items: end;
            gap: 10px;
            height: 220px;
            padding: 10px 4px 0;
        }
        .chart-bar-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: end;
            align-items: center;
            gap: 8px;
            height: 100%;
            min-width: 24px;
            color: var(--muted);
            font-size: 10px;
            text-align: center;
        }
        .chart-bar {
            width: 100%;
            max-width: 48px;
            min-height: 8px;
            border-radius: 8px 8px 0 0;
            background: linear-gradient(180deg, #67d5ff 0%, #4c82ff 100%);
            box-shadow: inset 0 -6px 0 rgba(255,255,255,0.12);
        }
        .report-card-header {
            background: var(--surface2);
            border-bottom: 1px solid var(--border);
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        .report-card-title {
            font-size: 18px;
            font-weight: 700;
        }
        .report-status {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
        }
        .report-table-wrap {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }
        th {
            background: var(--surface2);
            color: var(--muted);
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 12px 16px;
            text-align: left;
        }
        td {
            border-top: 1px solid var(--border);
            padding: 14px 16px;
            color: var(--text);
            font-size: 14px;
        }
        .empty-block {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 30px 22px;
            color: var(--muted);
            text-align: center;
        }
        @media (max-width: 768px) {
            .report-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .report-actions {
                width: 100%;
            }
            .report-actions .btn {
                flex: 1;
            }
        }
    </style>

    <div class="report-shell">
        <form id="reportFilterForm" method="GET" action="{{ route('reports.index') }}" class="report-toolbar">
            <div class="report-field">
                <label class="form-label">Report type</label>
                <select name="type" class="report-value">
                    @foreach($allowedTypes as $type)
                        <option value="{{ $type }}" {{ $selectedType === $type ? 'selected' : '' }}>
                            {{ $labels[$type] ?? ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="report-field">
                <label class="form-label">Period</label>
                <select name="range" id="reportRange" class="report-value">
                    <option value="day" {{ $range === 'day' ? 'selected' : '' }}>Day</option>
                    <option value="week" {{ $range === 'week' ? 'selected' : '' }}>Week</option>
                    <option value="month" {{ $range === 'month' ? 'selected' : '' }}>Month</option>
                </select>
            </div>

            <div class="report-field">
                <label for="reportCalendarInput" class="form-label" id="reportCalendarLabel">
                    @if($range === 'week') Selected week
                    @elseif($range === 'month') Selected month
                    @else Selected day
                    @endif
                </label>
                <input
                    id="reportCalendarInput"
                    name="date"
                    class="report-value"
                    value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                    type="{{ $range === 'week' ? 'week' : ($range === 'month' ? 'month' : 'date') }}"
                >
            </div>

            <div class="report-actions">
                <button type="submit" class="btn btn-primary">Generate report</button>
                <button type="button" class="btn btn-secondary" data-export="excel">Export Excel</button>
                <button type="button" class="btn btn-secondary" data-export="pdf">Export PDF</button>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('reportFilterForm');
                const rangeSelect = document.getElementById('reportRange');
                const calendarInput = document.getElementById('reportCalendarInput');
                const calendarLabel = document.getElementById('reportCalendarLabel');
                const exportButtons = document.querySelectorAll('[data-export]');

                function applyRangeCalendar() {
                    const type = rangeSelect.value;
                    if (type === 'week') {
                        calendarInput.type = 'week';
                        calendarLabel.textContent = 'Selected week';
                    } else if (type === 'month') {
                        calendarInput.type = 'month';
                        calendarLabel.textContent = 'Selected month';
                    } else {
                        calendarInput.type = 'date';
                        calendarLabel.textContent = 'Selected day';
                    }

                    if (!calendarInput.value) {
                        const now = new Date();
                        const iso = now.toISOString().slice(0, 10);
                        calendarInput.value = type === 'month' ? iso.slice(0, 7) : (type === 'week' ? iso : iso);
                    }
                }

                const chartContainer = document.querySelector('.chart-bars[data-chart]');
                if (chartContainer) {
                    const chart = JSON.parse(chartContainer.dataset.chart || '{}');
                    const values = Array.isArray(chart.values) ? chart.values : [];
                    const labels = Array.isArray(chart.labels) ? chart.labels : [];
                    if (values.length) {
                        const max = Math.max(...values, 1);
                        const bars = values.map(function (value, index) {
                            const height = Math.max(8, (value / max) * 100);
                            const label = labels[index] || '';
                            return '<div class="chart-bar-wrap"><div class="chart-bar" style="height: ' + height + '%;"></div><span>' + label + '</span></div>';
                        }).join('');
                        chartContainer.innerHTML = bars;
                    }
                }

                rangeSelect.addEventListener('change', applyRangeCalendar);
                applyRangeCalendar();

                exportButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        const formData = new FormData(form);
                        const params = new URLSearchParams(formData);
                        params.set('format', button.dataset.export);
                        const url = '{{ route('reports.export') }}' + '?' + params.toString();
                        window.open(url, '_blank');
                    });
                });
            });
        </script>

        <div class="report-grid">
            @foreach($report['stats'] as $stat)
                <div class="report-stat {{ $stat['tone'] ?? 'green' }}">
                    <div class="report-stat-label">{{ $stat['label'] }}</div>
                    <div class="report-stat-value">{{ $stat['value'] }}</div>
                </div>
            @endforeach
        </div>

        @if(!empty($report['chart']['labels'] ?? []))
            <div class="report-card">
                <div class="report-card-header">
                    <div class="report-card-title">Analytics</div>
                    <div class="report-status">Trend for the selected period</div>
                </div>
                <div class="report-chart">
                    <div class="chart-bars" data-chart='@json($report['chart'])'></div>
                </div>
            </div>
        @endif

        @if($report['empty'])
            <div class="empty-block">
                No records found for this report in the selected period.
            </div>
        @else
            <div class="report-card">
                <div class="report-card-header">
                    <div class="report-card-title">{{ $report['title'] }}</div>
                    <div class="report-status">{{ $report['subtitle'] }}</div>
                </div>

                <div class="report-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                @if($selectedType === 'payment')
                                    <th>Member</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                @elseif($selectedType === 'attendance')
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Duration</th>
                                @elseif($selectedType === 'member')
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Date Joined</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                @else
                                    <th>Member</th>
                                    <th>Session</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Instructor</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['rows'] as $row)
                                <tr>
                                    @if($selectedType === 'payment')
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['type'] }}</td>
                                        <td>{{ $row['date'] }}</td>
                                        <td>{{ $row['method'] }}</td>
                                        <td>{{ $row['amount'] }}</td>
                                    @elseif($selectedType === 'attendance')
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['role'] }}</td>
                                        <td>{{ $row['date'] }}</td>
                                        <td>{{ $row['time_in'] }}</td>
                                        <td>{{ $row['time_out'] }}</td>
                                        <td>{{ $row['duration'] }}</td>
                                    @elseif($selectedType === 'member')
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['email'] }}</td>
                                        <td>{{ $row['date'] }}</td>
                                        <td>{{ $row['plan'] }}</td>
                                        <td>{{ $row['status'] }}</td>
                                    @else
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['title'] }}</td>
                                        <td>{{ $row['date'] }}</td>
                                        <td>{{ $row['status'] }}</td>
                                        <td>{{ $row['instructor'] }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
