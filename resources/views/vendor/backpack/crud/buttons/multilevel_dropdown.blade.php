<div class="dropdown-multilevel">
    <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
        <i class="la la-print"></i>
    </button>
    <ul class="dropdown-menu">

        @foreach ($list as $item)
            @if (is_array($item['url']))
                <li class="dropdown-item dropdown-submenu">
                    <span class="test" tabindex="-1" href="#">
                        {{ $item['label'] }}
                        <i class="las la-angle-left"></i>
                    </span>
                    <ul class="dropdown-menu">
                        @foreach ($item['url'] as $subitem)
                            <li class="dropdown-item">
                                <a target="_black" tabindex="-1" href="{{ $subitem['url'] }}">
                                    {{ $subitem['label'] }}
                                </a>
                            </li>
                        @endforeach


                    </ul>
                </li>
            @else
                <li class="dropdown-item">
                    <a target="_black" tabindex="-1" href="#">
                        {{ $item['label'] }}
                    </a>
                </li>
            @endif
        @endforeach


    </ul>
</div>
