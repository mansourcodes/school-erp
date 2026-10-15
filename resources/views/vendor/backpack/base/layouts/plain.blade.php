<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ config('backpack.base.html_direction') }}" data-admin-theme="{{ \App\Support\AdminTheme::current() }}">
<head>
    @include(backpack_view('inc.head'))
    <link rel="stylesheet" type="text/css" href="{{ asset('css/admin-themes.css').'?v='.filemtime(public_path('css/admin-themes.css')) }}">
</head>
<body class="app flex-row align-items-center">

  @yield('header')

  <div class="container">
  @yield('content')
  </div>

  <footer class="app-footer sticky-footer">
    @include('backpack::inc.footer')
  </footer>

  @yield('before_scripts')
  @stack('before_scripts')

  @include(backpack_view('inc.scripts'))

  @yield('after_scripts')
  @stack('after_scripts')

</body>
</html>
