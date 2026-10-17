{{--
    Dashboard donut card. $chart comes from App\Support\AdminDashboard (total, centre, slices with a colour slot and an optional code);
    $title, $subtitle, $centreLabel and $empty are display texts. Counts show on hover; the key sits under the ring.
--}}
@php
    $radius = 80;
    $circumference = 2 * M_PI * $radius;
    $shown = array_values(array_filter($chart['slices'], function ($slice) { return $slice['count'] > 0; }));
    $gap = count($shown) > 1 ? 2 : 0; // 2px surface gap between segments
    $offset = 0;
@endphp

<section class="dash-chart h-100">
    <header class="dash-chart-head">
        <h3>{{ $title }}</h3>
        <small>{{ $subtitle }}</small>
    </header>

    @if ($chart['total'] === 0)
        <p class="dash-chart-empty">{{ $empty }}</p>
    @else
        <svg class="dash-donut" viewBox="0 0 200 200" role="img" aria-label="{{ $centreLabel }} {{ $chart['centre'] }}">
            @if (collect($shown)->contains(function ($slice) { return \Illuminate\Support\Str::endsWith((string) $slice['slot'], '-excused'); }))
                {{-- Excused = the same hue with a 45° white hatch (portal .hatch); referenced from the CSS slots --}}
                <defs>
                    @foreach (['late', 'absent'] as $hue)
                        <pattern id="dash-hatch-{{ $hue }}" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                            <rect class="dash-hatch-{{ $hue }}" width="6" height="6" />
                            <rect width="2" height="6" fill="#ffffff" fill-opacity=".55" />
                        </pattern>
                    @endforeach
                </defs>
            @endif
            <circle class="dash-donut-track" cx="100" cy="100" r="{{ $radius }}" />
            <g transform="rotate(-90 100 100)">
                @foreach ($shown as $slice)
                    @php $length = $circumference * $slice['count'] / $chart['total']; @endphp
                    <circle class="dash-donut-seg dash-slot-{{ $slice['slot'] }}" cx="100" cy="100" r="{{ $radius }}"
                            stroke-dasharray="{{ max($length - $gap, 0.5) }} {{ $circumference }}"
                            stroke-dashoffset="{{ -$offset }}">
                        <title>{{ $slice['label'] }}: {{ number_format($slice['count']) }} ({{ number_format($slice['percent'], 1) }}%)</title>
                    </circle>
                    @php $offset += $length; @endphp
                @endforeach
            </g>
            <text class="dash-donut-value" x="100" y="100">{{ $chart['centre'] }}</text>
            <text class="dash-donut-label" x="100" y="128">{{ $centreLabel }}</text>
        </svg>

        <ul class="dash-legend">
            @foreach ($chart['slices'] as $slice)
                <li title="{{ number_format($slice['count']) }} ({{ number_format($slice['percent'], 1) }}%)">
                    @if (isset($slice['code']))
                        <span class="dash-legend-key dash-legend-code dash-slot-{{ $slice['slot'] }}">{{ $slice['code'] }}</span>{{ $slice['label'] }}
                    @else
                        <span class="dash-legend-key dash-slot-{{ $slice['slot'] }}"></span>{{ $slice['label'] }}
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>
