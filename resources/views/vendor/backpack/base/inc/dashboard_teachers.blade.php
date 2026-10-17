{{--
    Dashboard teacher ranking card (App\Support\AdminDashboard::teachers()). $rows: [{id, name, avatar, sessions, classrooms}],
    $max: the top teacher's sessions (bar scale shared by both cards), $tone: most | least, $title,
    $othersShown: the "most" card already lists every teacher (changes the empty text).
--}}
<section class="dash-chart dash-rank dash-rank-{{ $tone }} h-100">
    <header class="dash-chart-head">
        <h3><i class="la {{ $tone === 'most' ? 'la-arrow-up' : 'la-arrow-down' }}"></i> {{ $title }}</h3>
        <small>{{ __('base.dashboard.teachers_scope') }}</small>
    </header>

    @if (! $rows)
        <p class="dash-chart-empty">{{ __(! empty($othersShown) ?'base.dashboard.teachers_no_others' : 'base.dashboard.teachers_empty') }}</p>
    @else
        <ol class="dash-rank-list">
            @foreach ($rows as $index => $row)
                <li>
                    <span class="dash-rank-pos">{{ $index + 1 }}</span>
                    <img class="dash-rank-avatar" src="{{ $row['avatar'] }}" alt="">
                    <div class="dash-rank-body">
                        <div class="dash-rank-line">
                            <span class="dash-rank-name">{{ $row['name'] }}</span>
                            <span class="dash-rank-count">{{ trans_choice('base.dashboard.teachers_sessions', $row['sessions']) }}</span>
                        </div>
                        <div class="dash-rank-bar" title="{{ trans_choice('base.dashboard.teachers_classrooms', $row['classrooms']) }}">
                            <span style="width: {{ round($row['sessions'] * 100 / $max, 1) }}%"></span>
                        </div>
                        <small class="dash-rank-meta">{{ trans_choice('base.dashboard.teachers_classrooms', $row['classrooms']) }}</small>
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</section>
