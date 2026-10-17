@if (backpack_auth()->check())
    {{-- Sidebar: the menu takes 80% of the height and scrolls; the source link (was the page footer) sits under it. --}}
    <div class="{{ config('backpack.base.sidebar_class') }}">
      <nav class="sidebar-nav overflow-hidden">
        <ul class="nav">
          @include(backpack_view('inc.sidebar_content'))
        </ul>
      </nav>

      @if (config('backpack.base.developer_link') && config('backpack.base.developer_name'))
        <div class="sidebar-foot">
          <div class="sidebar-card">
            <i class="lab la-github sidebar-card-icon" aria-hidden="true"></i>
            {{ trans('backpack::base.handcrafted_by') }}
            <a target="_blank" rel="noopener" href="{{ config('backpack.base.developer_link') }}">{{ config('backpack.base.developer_name') }}</a>.
          </div>
        </div>
      @endif
    </div>
@endif

{{-- Same scripts as Backpack's inc/sidebar: remember the collapsed state, mark the current link active. --}}
@push('before_scripts')
  <script type="text/javascript">
    // Save default sidebar class
    let sidebarClass = (document.body.className.match(/sidebar-(sm|md|lg|xl)-show/) || ['sidebar-lg-show'])[0];
    let sidebarTransition = value => document.querySelector('.app-body > .sidebar').style.transition = value || '';

    // Recover sidebar state
    let sessionState = sessionStorage.getItem('sidebar-collapsed');
    if (sessionState) {
      // disable the transition animation temporarily, so that if you're browsing across
      // pages with the sidebar closed, the sidebar does not flicker into the view
      sidebarTransition("none");
      document.body.classList.toggle(sidebarClass, sessionState === '1');

      // re-enable the transition, so that if the user clicks the hamburger menu, it does have a nice transition
      setTimeout(sidebarTransition, 100);
    }
  </script>
@endpush

@push('after_scripts')
  <script>
      // Store sidebar state
      document.querySelectorAll('.sidebar-toggler').forEach(toggler =>
        toggler.addEventListener('click', () =>
          sessionStorage.setItem('sidebar-collapsed', Number(!document.body.classList.contains(sidebarClass)))
        )
      );
      // Set active state on menu element
      var full_url = "{{ Request::fullUrl() }}";
      var $navLinks = $(".sidebar-nav li a, .app-header li a");

      // First look for an exact match including the search string
      var $curentPageLink = $navLinks.filter(
          function() { return $(this).attr('href') === full_url; }
      );

      // If not found, look for the link that starts with the url
      if(!$curentPageLink.length > 0){
          $curentPageLink = $navLinks.filter( function() {
            if ($(this).attr('href').startsWith(full_url)) {
              return true;
            }

            if (full_url.startsWith($(this).attr('href'))) {
              return true;
            }

            return false;
          });
      }

      // for the found links that can be considered current, make sure
      // - the parent item is open
      $curentPageLink.parents('li').addClass('open');
      // - the actual element is active
      $curentPageLink.each(function() {
        $(this).addClass('active');
      });

      // Section titles open / close the links under them (up to the next title). Closed sections are
      // remembered in this browser; the section of the current page always opens.
      (function () {
        var key = 'sidebar-closed-sections', closed = [];
        try { closed = JSON.parse(localStorage.getItem(key)) || []; } catch (e) {}

        document.querySelectorAll('.sidebar-nav .nav-title').forEach(function (title) {
          var name = title.textContent.trim(), items = [], el = title.nextElementSibling;
          while (el && !el.classList.contains('nav-title')) {
            if (el.classList.contains('nav-item')) items.push(el);
            el = el.nextElementSibling;
          }
          if (!items.length) return;

          var set = function (open, save) {
            title.setAttribute('aria-expanded', open ? 'true' : 'false');
            items.forEach(function (item) { item.classList.toggle('nav-section-closed', !open); });
            if (!save) return;
            closed = closed.filter(function (n) { return n !== name; });
            if (!open) closed.push(name);
            try { localStorage.setItem(key, JSON.stringify(closed)); } catch (e) {}
          };

          var hasCurrent = items.some(function (item) { return item.querySelector('.nav-link.active'); });
          title.setAttribute('role', 'button');
          title.tabIndex = 0;
          set(hasCurrent || closed.indexOf(name) === -1, false);

          title.addEventListener('click', function () { set(title.getAttribute('aria-expanded') !== 'true', true); });
          title.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); title.click(); }
          });
        });
      })();
  </script>
@endpush
