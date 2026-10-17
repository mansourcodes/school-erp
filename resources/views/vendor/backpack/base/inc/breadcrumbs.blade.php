{{-- Breadcrumbs, shown in the top navbar (main_header) instead of above the page. --}}
@if (config('backpack.base.breadcrumbs') && isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs))
  <nav aria-label="breadcrumb" class="header-crumbs d-none d-lg-flex">
    <ol class="breadcrumb">
      @foreach ($breadcrumbs as $label => $link)
        @if ($link)
          <li class="breadcrumb-item"><a href="{{ $link }}">{{ $label }}</a></li>
        @else
          <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
        @endif
      @endforeach
    </ol>
  </nav>
@endif
