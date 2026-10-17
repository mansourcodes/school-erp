@extends(backpack_view('blank'))

@php
    $shortcuts = [];
    if (backpack_user()->hasRole('Superadmin')) {
        $shortcuts = [
            ['url' => backpack_url('course'), 'icon' => 'la-campground', 'label' => trans('course.courses')],
            ['url' => backpack_url('student'), 'icon' => 'la-user-graduate', 'label' => trans('student.students')],
            ['url' => backpack_url('classroom'), 'icon' => 'la-chalkboard-teacher', 'label' => trans('classroom.classrooms')],
            ['url' => backpack_url('payment'), 'icon' => 'la-file-invoice-dollar', 'label' => trans('account.payments')],
        ];
    }
    $shortcuts[] = ['url' => backpack_url('add_attend_by_date'), 'icon' => 'la-check-square', 'label' => trans('attend.add_attend_w_date')];

    $tiles = $stats ? [
        ['value' => $stats['active_courses'], 'icon' => 'la-campground', 'label' => __('base.dashboard.active_courses')],
        ['value' => $stats['active_classrooms'], 'icon' => 'la-chalkboard', 'label' => __('base.dashboard.active_classrooms')],
        ['value' => $stats['active_students'], 'icon' => 'la-user-graduate', 'label' => __('base.dashboard.active_students')],
        ['value' => $stats['students'], 'icon' => 'la-users', 'label' => __('base.dashboard.students')],
    ] : [];
@endphp

@push('after_styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Amiri+Quran&family=Amiri:wght@400;700&display=swap">
@endpush

@section('content')
<div class="dash">
    <div class="row">
        <div class="col-lg-7 mb-4">
            <section class="dash-hero h-100">
                <p class="dash-hero-date">{{ now()->locale('ar')->translatedFormat('l، j F Y') }}</p>
                <h1 class="dash-hero-title">{{ __('base.dashboard.greeting', ['name' => backpack_user()->name]) }}</h1>
                <p class="dash-hero-sub">{{ __('base.dashboard.subtitle') }}</p>
                <img class="dash-hero-logo" src="{{ \App\Support\AdminBranding::logoUrl() ?: asset('beta/logo-icon.svg') }}" alt="" aria-hidden="true">
            </section>
        </div>

        <div class="col-lg-5 mb-4">
            {{-- Mushaf page (design 2 of _brand/new_admin_design/quran-card-samples.html) --}}
            <section class="dash-quran h-100" aria-label="{{ __('base.dashboard.quran_title') }}" data-quotes='@json(\App\Support\QuranQuotes::all())'>
                <button type="button" class="dash-quran-next" title="{{ __('base.dashboard.quran_next') }}" aria-label="{{ __('base.dashboard.quran_next') }}">
                    <i class="la la-sync"></i>
                </button>
                <div class="dash-quran-frame">
                    <blockquote class="dash-quran-body dash-quran-{{ $quote['size'] }}" data-quran-body
                                title="{{ __('base.dashboard.quran_ref', ['surah' => $quote['surah'], 'ayah' => $quote['ayah']]) }}"
                                data-template="{{ __('base.dashboard.quran_ref', ['surah' => ':surah', 'ayah' => ':ayah']) }}">
                        <p class="dash-quran-text">
                            <span data-quran-text>{{ $quote['text'] }}</span>
                            <span class="dash-quran-number" data-quran-number>{{ $quote['number'] }}</span>
                        </p>
                        <span class="dash-quran-surah" data-quran-surah>{{ __('base.dashboard.quran_surah', ['surah' => $quote['surah']]) }}</span>
                    </blockquote>
                </div>
            </section>
        </div>
    </div>

    <h2 class="dash-section">{{ __('base.dashboard.shortcuts') }}</h2>
    <div class="row">
        @foreach ($shortcuts as $shortcut)
            <div class="col-6 col-md-4 col-xl mb-4">
                <a class="dash-link" href="{{ $shortcut['url'] }}">
                    <span class="dash-link-icon"><i class="la {{ $shortcut['icon'] }}"></i></span>
                    <span class="dash-link-label">{{ $shortcut['label'] }}</span>
                </a>
            </div>
        @endforeach
    </div>

    @if ($tiles)
        <h2 class="dash-section">{{ __('base.dashboard.stats') }}</h2>
        <div class="row">
            @foreach ($tiles as $tile)
                <div class="col-6 col-xl-3 mb-4">
                    <div class="dash-tile">
                        <span class="dash-tile-icon"><i class="la {{ $tile['icon'] }}"></i></span>
                        <div>
                            <div class="dash-tile-value">{{ number_format($tile['value']) }}</div>
                            <div class="dash-tile-label">{{ $tile['label'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                @include(backpack_view('inc.dashboard_donut'), [
                    'chart' => $attendance,
                    'title' => __('base.dashboard.attendance'),
                    'subtitle' => __('base.dashboard.attendance_scope'),
                    'centreLabel' => __('base.dashboard.attendance_rate'),
                    'empty' => __('base.dashboard.attendance_empty'),
                ])
            </div>
            <div class="col-md-6 mb-4">
                @include(backpack_view('inc.dashboard_donut'), [
                    'chart' => $classrooms,
                    'title' => __('base.dashboard.classrooms'),
                    'subtitle' => __('base.dashboard.classrooms_scope'),
                    'centreLabel' => __('base.dashboard.classrooms_centre'),
                    'empty' => __('base.dashboard.classrooms_empty'),
                ])
            </div>
        </div>
        @php $maxSessions = max(1, $teachers['most'] ? $teachers['most'][0]['sessions'] : 0); @endphp
        <div class="row">
            <div class="col-md-6 mb-4">
                @include(backpack_view('inc.dashboard_teachers'), ['rows' => $teachers['most'], 'max' => $maxSessions, 'tone' => 'most', 'title' => __('base.dashboard.teachers_most')])
            </div>
            <div class="col-md-6 mb-4">
                @include(backpack_view('inc.dashboard_teachers'), ['rows' => $teachers['least'], 'max' => $maxSessions, 'tone' => 'least', 'othersShown' => (bool) $teachers['most'], 'title' => __('base.dashboard.teachers_least')])
            </div>
        </div>

        <div class="dash-refresh-bar">
            <form method="POST" action="{{ route('admin.dashboard.refresh') }}" class="dash-refresh">
                @csrf
                <small title="{{ \Carbon\Carbon::parse($computed_at)->locale('ar')->translatedFormat('j F Y، H:i') }}">
                    {{ __('base.dashboard.computed_at', ['time' => \Carbon\Carbon::parse($computed_at)->locale('ar')->diffForHumans()]) }}
                </small>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="la la-sync"></i> {{ __('base.dashboard.refresh') }}
                </button>
            </form>
        </div>
    @endif

</div>
@endsection

@push('after_scripts')
<script>
    (function () {
        var card = document.querySelector('.dash-quran');
        if (!card) return;
        var quotes = JSON.parse(card.getAttribute('data-quotes'));
        var body = card.querySelector('[data-quran-body]');
        var text = card.querySelector('[data-quran-text]');
        var surah = card.querySelector('[data-quran-surah]');
        var number = card.querySelector('[data-quran-number]');
        var surahTemplate = @json(__('base.dashboard.quran_surah', ['surah' => ':surah']));
        card.querySelector('.dash-quran-next').addEventListener('click', function () {
            var current = text.textContent, quote;
            do { quote = quotes[Math.floor(Math.random() * quotes.length)]; } while (quotes.length > 1 && quote.text === current);
            text.textContent = quote.text;
            number.textContent = quote.number;
            surah.textContent = surahTemplate.replace(':surah', quote.surah);
            body.title = body.getAttribute('data-template').replace(':surah', quote.surah).replace(':ayah', quote.ayah);
            body.className = body.className.replace(/dash-quran-(lg|md|sm)/, 'dash-quran-' + quote.size);
        });
    })();
</script>
@endpush
