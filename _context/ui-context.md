# UI Context

## Theme

Two distinct UI surfaces in the same app:

**Admin panel** — Backpack CRUD's default Backstrap theme (dark sidebar, light
content area). RTL (`html_direction = rtl` in `config/backpack/base.php`).
Custom overrides in `resources/sass/custom/backpack.rtl.scss`.

**Student portal** — Light theme. Bootstrap 5 RTL CDN. Body background
`#b7b7b6` (grey). White navbar. White cards on grey page background.
No dark mode.

## Colors

### Student Portal (custom inline styles in `layouts/app.blade.php`)

| Role            | Value        | Notes                          |
| --------------- | ------------ | ------------------------------ |
| Page background | `#b7b7b6`    | Medium grey body               |
| Navbar / cards  | `#ffffff`    | White                          |
| Table header    | `#cccccc`    | Light grey (`th` background)   |
| Table border    | `black`      | 1px solid on all td/th         |

No CSS custom properties are in use yet — colors are hardcoded in the layout
`<style>` block. New work should extract these to variables.

### Admin Panel

Backpack Backstrap default palette. Custom accent changes are in
`resources/sass/custom/global.scss` (e.g. absent-switch colour `#161c2d`).

## Typography

| Role    | Font                | Source                          |
| ------- | ------------------- | ------------------------------- |
| UI text | Nunito              | Google Fonts CDN                |
| Arabic  | System/browser font | No custom Arabic font loaded    |

`$font-family-sans-serif: 'Nunito', sans-serif` set in `_variables.scss`.

## Direction

RTL throughout (`dir="rtl"` on `<html>`). Bootstrap 5 RTL build is used for
the student portal. Backpack has its own RTL config.

## Component Library

**Admin**: Backpack CRUD 4.1 components (lists, forms, modals, buttons).
Custom button views live in `resources/views/vendor/backpack/crud/buttons/`.
`multilevel_dropdown` is a custom dropdown button used extensively on list
views for per-row print/report actions.

**Student portal**: Vanilla Bootstrap 5 RTL components. Cards with
`data-bs-toggle="collapse"` for expandable course sections. No JS component
framework.

## Layout Patterns

- **Admin sidebar**: Fixed Backpack left sidebar with top navbar (RTL flipped
  to right)
- **Student portal**: Top navbar (white bg) + centered `.container` main
  content, max `col-md-8`
- **Student course cards**: Card per enrollment, with collapsible `card-header`
  sections (timetable, payments, certificate)
- **Print/PDF views**: Separate `layouts/pdf.blade.php` and
  `layouts/print.blade.php`; table-heavy, no sidebar, Arabic RTL

## Table Styles (shared across reports)

```css
table.table           { border-collapse: collapse; margin: 1%; width: 98%; }
table.table td, th    { padding: 5px; border: 1px solid black; }
table.table th        { background-color: #ccc; }
table.table.table-no-border td/th  { border: 0; background: transparent; }
table.table.table-ziped td/th      { padding: 2px 15px; font-size: 14px; }
```

## Icons

**Student portal**: Heroicons inline SVG (`stroke-width="1.5"`, `style="width:16px"` inline).

**Admin panel**: Font Awesome (loaded via Backpack bundle).
FA icon sizes follow Backpack conventions.

## Localization Keys

All user-visible strings go through `__('localize.key')` or translation
helpers. Key files: `resources/lang/ar/localize.php`, `en/localize.php`,
plus domain files (`reports.php`, `account.php`, `examtool.php`,
`studentmark.php`, `crud.php`).
