// Mirrors features/portal-theme: localStorage "gh-theme" = the last theme the server sent.
(function () {
  var colors = { ghadeer: '#ffffff', girls: '#fffcfb', cupcake: '#faf7f5', autumn: '#f1f1f1', wireframe: '#ffffff' };
  var theme = 'ghadeer';
  try {
    var saved = localStorage.getItem('gh-theme');
    if (saved && Object.prototype.hasOwnProperty.call(colors, saved)) theme = saved;
  } catch (e) { /* private mode */ }
  document.documentElement.setAttribute('data-theme', theme);
  var meta = document.querySelector('meta[name="theme-color"]');
  if (meta) meta.setAttribute('content', colors[theme]);
})();
