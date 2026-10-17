{{-- Top navbar (_brand/new_admin_design, sample 2): brand over the sidebar column, page breadcrumbs, user. --}}
<header class="{{ config('backpack.base.header_class') }}">
  <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto ml-3" type="button" data-toggle="sidebar-show" aria-label="{{ trans('backpack::base.toggle_navigation')}}">
    <span class="navbar-toggler-icon"></span>
  </button>

  <a class="navbar-brand" href="{{ url(config('backpack.base.home_link')) }}" title="{{ config('backpack.base.project_name') }}">
    <span class="brand-logo">
      <img src="{{ \App\Support\AdminBranding::logoUrl() ?: asset('beta/logo-icon.svg') }}" alt="">
    </span>
    <span class="brand-text">
      {{ \App\Support\PortalBranding::get('centre') ?: config('backpack.base.project_name') }}
      <small>{{ __('base.admin_panel') }}</small>
    </span>
  </a>

  <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" data-toggle="sidebar-lg-show" aria-label="{{ trans('backpack::base.toggle_navigation')}}">
    <span class="navbar-toggler-icon"></span>
  </button>

  @includeWhen(isset($breadcrumbs), backpack_view('inc.breadcrumbs'))

  @if (backpack_auth()->check())
    @php
      $adminUser = backpack_user();
      $adminRole = $adminUser->getRoleNames()->first();
    @endphp
    <ul class="nav navbar-nav header-end">
      @foreach (\App\Support\ActiveCourses::terms() as $term)
        <li class="nav-item d-none d-md-flex">
          <a class="header-term" href="{{ backpack_url('course') }}" title="{{ __('base.active_courses') }}: {{ implode('، ', $term['names']) }}">
            <i class="la la-calendar"></i>
            {{ $term['label'] }}
            @if ($term['count'] > 1)
              <span class="header-term-count">{{ trans_choice('base.courses_count', $term['count'], ['count' => $term['count']]) }}</span>
            @endif
          </a>
        </li>
      @endforeach

      @include(backpack_view('inc.topbar_right_content'))

      <li class="nav-item dropdown">
        <a class="nav-link header-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
          <span class="header-user-text d-none d-md-inline">
            {{ $adminUser->name }}
            @if ($adminRole)
              <small>{{ \Illuminate\Support\Facades\Lang::has('base.roles.'.$adminRole) ? __('base.roles.'.$adminRole) : $adminRole }}</small>
            @endif
          </span>
          <img class="img-avatar" src="{{ backpack_avatar_url($adminUser) }}" alt="{{ $adminUser->name }}">
        </a>
        <div class="dropdown-menu {{ config('backpack.base.html_direction') == 'rtl' ? 'dropdown-menu-left' : 'dropdown-menu-right' }} pb-1 pt-1">
          <a class="dropdown-item" href="{{ route('backpack.account.info') }}"><i class="la la-user"></i> {{ trans('backpack::base.my_account') }}</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="{{ backpack_url('logout') }}"><i class="la la-lock"></i> {{ trans('backpack::base.logout') }}</a>
        </div>
      </li>
    </ul>
  @endif
</header>
