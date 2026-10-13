// Mirrors features/theme-switch: localStorage "gh-theme" = system | light | dark.
(function () {
  var mode = 'system';
  try { mode = localStorage.getItem('gh-theme') || 'system'; } catch (e) { /* private mode */ }
  var dark = mode === 'dark' || (mode === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
  document.documentElement.setAttribute('data-theme', dark ? 'ghadeer-night' : 'ghadeer');
  var meta = document.querySelector('meta[name="theme-color"]');
  if (meta) meta.setAttribute('content', dark ? '#14201e' : '#ffffff');
})();
