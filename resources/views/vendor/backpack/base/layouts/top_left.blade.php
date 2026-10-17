<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}" dir="{{ config('backpack.base.html_direction') }}" data-admin-theme="{{ \App\Support\AdminTheme::current() }}">

<head>
  @include(backpack_view('inc.head'))
  <link rel="stylesheet" type="text/css" href="{{ asset('css/admin-layout.css').'?v='.filemtime(public_path('css/admin-layout.css')) }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('css/admin-themes.css').'?v='.filemtime(public_path('css/admin-themes.css')) }}">

</head>

<body class="{{ config('backpack.base.body_class') }}">

  @include(backpack_view('inc.main_header'))

  <div class="app-body">

    @include(backpack_view('inc.sidebar'))

    <main class="main pt-2">

       @yield('before_breadcrumbs_widgets')

       {{-- breadcrumbs are in the top navbar (inc.main_header) --}}

       @yield('after_breadcrumbs_widgets')

       @yield('header')

        <div class="container-fluid animated fadeIn">

          @yield('before_content_widgets')

          @yield('content')
          
          @yield('after_content_widgets')

        </div>

    </main>

  </div><!-- ./app-body -->

  {{-- no page footer: its source link is under the sidebar menu (inc.sidebar) --}}

  @yield('before_scripts')
  @stack('before_scripts')

  @include(backpack_view('inc.scripts'))

  @yield('after_scripts')
  @stack('after_scripts')
</body>
</html>