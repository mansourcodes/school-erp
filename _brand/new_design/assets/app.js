/* ==========================================================================
   Al-Ghadeer Student Portal — design mockup runtime (Phase 1)
   --------------------------------------------------------------------------
   Plain JS stand-in for what becomes React in Phase 2:
     GH.shell()        → <AppShell/>  (sidebar on lg, bottom dock on mobile)
     GH.theme          → themeSlice   (light / dark / system)
     GH.DEMO           → RTK Query responses (shapes match PLAN.md §7)
     GH.ui.*           → presentational components
   Nothing here talks to a server.
   ========================================================================== */
(function () {
  "use strict";

  /* ---------------------------------------------------------------- theme */
  var THEME_KEY = "gh-theme";
  var mq = window.matchMedia ? window.matchMedia("(prefers-color-scheme: dark)") : null;
  function readMode() { try { return localStorage.getItem(THEME_KEY) || "system"; } catch (e) { return "system"; } }
  function writeMode(m) { try { localStorage.setItem(THEME_KEY, m); } catch (e) { /* private mode */ } }
  function applyTheme(mode) {
    var dark = mode === "dark" || (mode === "system" && mq && mq.matches);
    document.documentElement.setAttribute("data-theme", dark ? "ghadeer-night" : "ghadeer");
    document.documentElement.setAttribute("data-mode", mode);
    var meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) { meta = document.createElement("meta"); meta.name = "theme-color"; document.head.appendChild(meta); }
    meta.content = dark ? "#14201e" : "#ffffff";
  }
  applyTheme(readMode());
  if (mq && mq.addEventListener) mq.addEventListener("change", function () { if (readMode() === "system") applyTheme("system"); });

  var theme = {
    modes: ["system", "light", "dark"],
    labels: { system: "حسب الجهاز", light: "فاتح", dark: "داكن" },
    icons: { system: "monitor", light: "sun", dark: "moon" },
    get: readMode,
    set: function (m) { writeMode(m); applyTheme(m); syncThemeControls(); },
    cycle: function () { var i = theme.modes.indexOf(readMode()); theme.set(theme.modes[(i + 1) % 3]); }
  };
  function syncThemeControls() {
    var m = readMode();
    document.querySelectorAll("[data-theme-btn]").forEach(function (b) {
      b.innerHTML = '<i data-lucide="' + theme.icons[m] + '" class="size-5"></i>';
      b.setAttribute("aria-label", "المظهر: " + theme.labels[m]);
      b.setAttribute("title", "المظهر: " + theme.labels[m]);
    });
    document.querySelectorAll("[data-theme-set]").forEach(function (b) {
      b.classList.toggle("btn-active", b.getAttribute("data-theme-set") === m);
      b.setAttribute("aria-pressed", b.getAttribute("data-theme-set") === m);
    });
    icons();
  }

  /* -------------------------------------------------------------- helpers */
  var TODAY = new Date(2026, 9, 12);            // fixed demo "today": Mon 12 Oct 2026
  function parse(iso) { var p = iso.split("-"); return new Date(+p[0], +p[1] - 1, +p[2]); }
  function iso(d) { return d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0"); }
  var LOC = "ar-u-nu-latn";
  function fmtDate(isoStr, opts) { return new Intl.DateTimeFormat(LOC, opts || { day: "numeric", month: "long", year: "numeric" }).format(parse(isoStr)); }
  function fmtHijri(isoStr) {
    try { return new Intl.DateTimeFormat("ar-SA-u-ca-islamic-umalqura-nu-latn", { day: "numeric", month: "long", year: "numeric" }).format(parse(isoStr)); }
    catch (e) { return ""; }
  }
  function fmtTime(hm) { var p = hm.split(":"); var h = +p[0]; var s = h >= 12 ? "م" : "ص"; h = h % 12 || 12; return h + ":" + p[1] + " " + s; }
  function money(n) { return n.toFixed(3) + " د.ب"; }            // Bahraini dinar: 3 decimals
  function pct(n) { return (Math.round(n * 10) / 10).toLocaleString("en", { maximumFractionDigits: 1 }) + "%"; }
  var DAYS = ["الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"];
  var DAYS_SHORT = ["أحد", "إثنين", "ثلاثاء", "أربعاء", "خميس", "جمعة", "سبت"];
  function esc(s) { return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }
  function icons() { if (window.lucide) window.lucide.createIcons({ attrs: { "stroke-width": 1.75 } }); }
  function mulberry32(a) { return function () { a |= 0; a = a + 0x6D2B79F5 | 0; var t = Math.imul(a ^ a >>> 15, 1 | a); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; }; }

  /* ------------------------------------------------- attendance statuses */
  // Codes match the admin "student_attend_list" report legend (✓ / ت / تع / ✗ / غع)
  var STATES = {
    present:        { label: "حاضر",        code: "✓",  icon: "circle-check", cls: "st-present",        attended: true },
    late:           { label: "متأخر",       code: "ت",  icon: "clock",        cls: "st-late",           attended: true },
    late_excused:   { label: "متأخر بعذر",  code: "تع", icon: "clock-alert",  cls: "st-late-excused",   attended: true, excused: true },
    absent:         { label: "غائب",        code: "✗",  icon: "circle-x",     cls: "st-absent" },
    absent_excused: { label: "غائب بعذر",   code: "غع", icon: "file-check",   cls: "st-absent-excused", excused: true },
    unrecorded:     { label: "لم يُرصد",     code: "—",  icon: "hourglass",    cls: "st-none" }
  };
  var STATE_ORDER = ["present", "late", "late_excused", "absent_excused", "absent"];
  function chip(state) {
    var s = STATES[state];
    return '<span class="st ' + s.cls + '"><i data-lucide="' + s.icon + '"></i>' + s.label + "</span>";
  }
  function pip(state) {
    var s = STATES[state];
    return '<span class="pip ' + s.cls + (s.excused ? " hatch" : "") + '" title="' + s.label + '">' + s.code + "</span>";
  }

  /* ------------------------------------------------------------ demo data */
  var MARK_LABELS = [
    { key: "finalexam", label: "الامتحان النهائي", max: 40 },
    { key: "midexam",   label: "الامتحان النصفي",  max: 20 },
    { key: "project",   label: "البحث / المشروع",  max: 10 },
    { key: "practice",  label: "التطبيق العملي",   max: 10 },
    { key: "memorise",  label: "الحفظ",            max: 10 },
    { key: "class",     label: "المشاركة الصفية",  max: 5 },
    { key: "attend",    label: "الحضور",           max: 5 }
  ];
  function marks(values) { return MARK_LABELS.map(function (m, i) { return { key: m.key, label: m.label, max: m.max, mark: values[i] }; }); }

  var CURRICULA_L2 = [
    { id: 11, name: "تجويد القرآن الكريم",   teacher: "الشيخ أحمد الستري",   slots: [{ day: 0, start: "16:00", end: "17:00" }, { day: 2, start: "16:00", end: "17:00" }] },
    { id: 12, name: "الفقه — العبادات",       teacher: "الشيخ محمد جعفر",     slots: [{ day: 0, start: "17:15", end: "18:15" }, { day: 2, start: "17:15", end: "18:15" }] },
    { id: 13, name: "العقائد",                teacher: "السيد حسن الموسوي",   slots: [{ day: 1, start: "16:00", end: "17:00" }, { day: 3, start: "16:00", end: "17:00" }] },
    { id: 14, name: "اللغة العربية — النحو",  teacher: "الأستاذ علي منصور",   slots: [{ day: 1, start: "17:15", end: "18:15" }, { day: 3, start: "17:15", end: "18:15" }] },
    { id: 15, name: "حفظ القرآن الكريم",      teacher: "الشيخ يوسف المرزوق",  slots: [{ day: 4, start: "16:00", end: "17:30" }] }
  ];
  var CURRICULA_L1 = [
    { id: 1, name: "التجويد — المستوى الأول", teacher: "الشيخ أحمد الستري",  slots: [{ day: 0, start: "16:00", end: "17:00" }, { day: 2, start: "16:00", end: "17:00" }] },
    { id: 2, name: "الفقه الميسّر",           teacher: "الشيخ محمد جعفر",    slots: [{ day: 0, start: "17:15", end: "18:15" }] },
    { id: 3, name: "أصول العقيدة",            teacher: "السيد حسن الموسوي",  slots: [{ day: 1, start: "16:00", end: "17:00" }] },
    { id: 4, name: "مبادئ النحو",             teacher: "الأستاذ علي منصور",  slots: [{ day: 3, start: "16:00", end: "17:00" }] },
    { id: 5, name: "حفظ جزء عمّ",              teacher: "الشيخ يوسف المرزوق", slots: [{ day: 4, start: "16:00", end: "17:00" }] }
  ];

  var DEMO = {
    today: iso(TODAY),
    student: {
      id: 158, name: "حسين علي عبدالله الحايكي", first_name: "حسين", initial: "ح",
      student_id: "20240158", cpr: "040512345", dob: "2004-05-18", age: 22,
      mobile: "36001234", mobile2: "17001234", email: "hussain.a@example.com",
      address: "المنامة — مجمع 304، طريق 412، منزل 18", registration_at: "2024-09-01",
      iqraa_id_account: "IQ-58214", degree: "بكالوريوس", photo_url: null
    },
    current: {
      id: 41, classroom_id: 912, status: "active",
      long_name: "الدورة الفصلية الأولى 2026 — المستوى الثاني", short_name: "المستوى الثاني",
      academic_path: "المسار الحوزوي", year: 2026, hijri_year: "1448", semester: "الفصل الأول",
      start_date: "2026-09-06", end_date: "2026-12-24", duration: 96,
      class_room_number: "2-ب", class_room_name: "قاعة الإمام الصادق — المبنى الرئيسي",
      curricula: CURRICULA_L2, bias: 0.82,
      payments: [
        { id: 1043, date: "2026-09-07", amount: 30, type: "رسوم الدورة", source: "نقدًا" },
        { id: 1102, date: "2026-10-05", amount: 15, type: "رسوم الكتب",  source: "بنفت باي" }
      ],
      marks_published: false,
      marks: marks([null, null, null, 8, null, 5, 4]),
      total_mark: null, final_grade: null
    },
    archived: [
      { id: 38, classroom_id: 870, status: "archived", long_name: "الدورة الفصلية الثانية 2026 — المستوى الأول", short_name: "المستوى الأول",
        academic_path: "المسار الحوزوي", year: 2026, hijri_year: "1447", semester: "الفصل الثاني",
        start_date: "2026-02-01", end_date: "2026-05-28", duration: 64, class_room_number: "1-أ", class_room_name: "قاعة الإمام الباقر",
        curricula: CURRICULA_L1, bias: 0.9,
        payments: [{ id: 911, date: "2026-02-02", amount: 30, type: "رسوم الدورة", source: "نقدًا" }, { id: 940, date: "2026-02-20", amount: 10, type: "رسوم الكتب", source: "نقدًا" }],
        marks_published: true, marks: marks([37, 18, 9, 9, 9.5, 5, 4]), total_mark: 91.5, final_grade: "ممتاز" },
      { id: 35, classroom_id: 801, status: "archived", long_name: "الدورة الفصلية الأولى 2025 — المستوى الأول", short_name: "المستوى الأول",
        academic_path: "المسار الحوزوي", year: 2025, hijri_year: "1447", semester: "الفصل الأول",
        start_date: "2025-09-07", end_date: "2025-12-25", duration: 64, class_room_number: "1-ج", class_room_name: "قاعة الإمام الباقر",
        curricula: CURRICULA_L1, bias: 0.84,
        payments: [{ id: 702, date: "2025-09-08", amount: 30, type: "رسوم الدورة", source: "تحويل بنكي" }],
        marks_published: true, marks: marks([33, 17, 8, 8, 8, 4.5, 4]), total_mark: 82.5, final_grade: "جيد جدًا" },
      { id: 31, classroom_id: 744, status: "archived", long_name: "الدورة التمهيدية 2025", short_name: "التمهيدية",
        academic_path: "المسار التمهيدي", year: 2025, hijri_year: "1446", semester: "الفصل الثاني",
        start_date: "2025-02-02", end_date: "2025-05-29", duration: 48, class_room_number: "ت-2", class_room_name: "القاعة متعددة الأغراض",
        curricula: CURRICULA_L1.slice(0, 3), bias: 0.78,
        payments: [],
        marks_published: true, marks: marks([30, 16, 7, 8, 7, 4, 3.5]), total_mark: 75.5, final_grade: "جيد جدًا" }
    ]
  };
  DEMO.courses = [DEMO.current].concat(DEMO.archived);
  function courseById(id) { return DEMO.courses.filter(function (c) { return String(c.id) === String(id); })[0] || DEMO.current; }

  // Deterministic sessions from the timetable (stand-in for GET /attendance)
  var _sessCache = {};
  function sessionsFor(course) {
    if (_sessCache[course.id]) return _sessCache[course.id];
    var rng = mulberry32(course.id * 97 + 13), out = [];
    var d = parse(course.start_date), end = parse(course.end_date);
    if (end >= TODAY) { end = new Date(TODAY); end.setDate(end.getDate() - 1); }
    for (; d <= end; d.setDate(d.getDate() + 1)) {
      course.curricula.forEach(function (c) {
        c.slots.forEach(function (s) {
          if (s.day !== d.getDay()) return;
          var r = rng(), b = course.bias, st;
          if (r < b) st = "present";
          else if (r < b + 0.06) st = "late";
          else if (r < b + 0.085) st = "late_excused";
          else if (r < b + 0.13) st = "absent_excused";
          else if (r < 0.985) st = "absent";
          else st = "unrecorded";
          out.push({ date: iso(d), start: s.start, end: s.end, curriculum_id: c.id, curriculum: c.name, teacher: c.teacher, state: st });
        });
      });
    }
    return (_sessCache[course.id] = out);
  }
  function summarize(sessions) {
    var c = { present: 0, late: 0, late_excused: 0, absent: 0, absent_excused: 0, unrecorded: 0 };
    sessions.forEach(function (s) { c[s.state]++; });
    var recorded = sessions.length - c.unrecorded;
    var attended = c.present + c.late + c.late_excused;
    return { counts: c, total: sessions.length, recorded: recorded, attended: attended,
             rate: recorded ? attended / recorded * 100 : 0,
             punctual: recorded ? c.present / recorded * 100 : 0,
             absences: c.absent + c.absent_excused };
  }
  function nextSession(course) {
    // demo "now" = 15:00 on TODAY
    for (var i = 0; i < 14; i++) {
      var d = new Date(TODAY); d.setDate(d.getDate() + i);
      var list = [];
      course.curricula.forEach(function (c) { c.slots.forEach(function (s) { if (s.day === d.getDay()) list.push({ c: c, s: s }); }); });
      list.sort(function (a, b) { return a.s.start < b.s.start ? -1 : 1; });
      if (list.length) return { date: iso(d), when: i === 0 ? "اليوم" : i === 1 ? "غدًا" : DAYS[d.getDay()], curriculum: list[0].c.name, teacher: list[0].c.teacher, start: list[0].s.start, end: list[0].s.end };
    }
    return null;
  }

  /* ---------------------------------------------------------- UI blocks */
  var ui = {};

  ui.avatar = function (size, textCls) {
    var st = DEMO.student, sz = size || "w-11 h-14";
    var inner = st.photo_url
      ? '<img data-avatar-img src="' + esc(st.photo_url) + '" alt="صورة الطالب" class="w-full h-full object-cover">'
      : '<span data-avatar-initial class="font-display ' + (textCls || "text-lg") + '">' + st.initial + "</span>";
    return '<div class="arch arch-ring ' + sz + ' shrink-0" data-avatar><div class="arch w-full h-full grid place-items-center bg-primary text-primary-content overflow-hidden">' + inner + "</div></div>";
  };

  ui.empty = function (icon, title, body, action) {
    return '<div class="flex flex-col items-center text-center py-10 px-6 gap-3">' +
      '<div class="arch w-16 h-20 bg-base-200 grid place-items-center text-primary"><i data-lucide="' + icon + '" class="size-7"></i></div>' +
      '<h3 class="font-display text-lg">' + title + '</h3><p class="text-sm opacity-70 max-w-sm">' + body + "</p>" + (action || "") + "</div>";
  };

  ui.courseMeta = function (c) {
    function row(icon, k, v) { return '<div class="flex gap-3 items-start"><i data-lucide="' + icon + '" class="size-4 mt-1.5 text-primary shrink-0"></i><div><dt>' + k + "</dt><dd>" + v + "</dd></div></div>"; }
    return '<dl class="kv grid grid-cols-2 gap-x-4 sm:gap-x-6 gap-y-4">' +
      row("layout-grid", "رقم الصف", '<span class="num">' + esc(c.class_room_number) + "</span>") +
      '<div class="col-span-2">' + row("map-pin", "مكان الدراسة", esc(c.class_room_name)) + "</div>" +
      row("calendar", "بداية الدراسة", fmtDate(c.start_date) + ' <span class="text-xs opacity-60 block">' + fmtHijri(c.start_date) + "</span>") +
      row("calendar-check", "نهاية الدراسة", fmtDate(c.end_date) + ' <span class="text-xs opacity-60 block">' + fmtHijri(c.end_date) + "</span>") +
      row("hourglass", "عدد الساعات المقررة", '<span class="num">' + c.duration + "</span> ساعة") +
      row("book-marked", "المسار", esc(c.academic_path)) +
      "</dl>";
  };

  ui.timetable = function (c, opts) {
    opts = opts || {};
    var byDay = {};
    c.curricula.forEach(function (cu) { cu.slots.forEach(function (s) { (byDay[s.day] = byDay[s.day] || []).push({ cu: cu, s: s }); }); });
    var days = [0, 1, 2, 3, 4, 5, 6].filter(function (d) { return byDay[d]; });
    days.forEach(function (d) { byDay[d].sort(function (a, b) { return a.s.start < b.s.start ? -1 : 1; }); });
    var today = opts.archived ? -1 : TODAY.getDay(), sel = days[0];
    if (!opts.archived) for (var i = 0; i < 7; i++) { var dd = (today + i) % 7; if (byDay[dd]) { sel = dd; break; } }

    function list(d) {
      return '<ul class="space-y-3">' + byDay[d].map(function (x) {
        return '<li class="flex gap-3"><div class="w-1 rounded-full bg-primary/70"></div><div class="min-w-0 flex-1">' +
          '<p class="font-medium leading-snug">' + esc(x.cu.name) + "</p>" +
          '<p class="text-sm opacity-70 flex flex-wrap gap-x-3"><span class="inline-flex items-center gap-1"><i data-lucide="clock" class="size-3.5"></i><span class="num">' + fmtTime(x.s.start) + " – " + fmtTime(x.s.end) + "</span></span>" +
          '<span class="inline-flex items-center gap-1"><i data-lucide="user-round" class="size-3.5"></i>' + esc(x.cu.teacher) + "</span></p></div></li>";
      }).join("") + "</ul>";
    }

    // phone: segmented day chips (thumb-sized) + the selected day only
    var phone = '<div class="sm:hidden" data-tt>' +
      '<div class="grid gap-1.5 mb-3" style="grid-template-columns:repeat(' + days.length + ',minmax(0,1fr))" role="tablist" aria-label="أيام الدراسة">' +
      days.map(function (d) {
        var on = d === sel;
        return '<button role="tab" aria-selected="' + on + '" data-tt-day="' + d + '" class="rounded-field min-h-12 py-1.5 flex flex-col items-center justify-center gap-0.5 text-sm transition-colors ' +
          (on ? "bg-primary text-primary-content" : "bg-base-200") + '">' + DAYS_SHORT[d] +
          (d === today ? '<span class="text-[10px] leading-none">اليوم</span>' : "") + "</button>";
      }).join("") + "</div>" +
      days.map(function (d) { return '<div class="card-soft p-4" data-tt-panel="' + d + '"' + (d === sel ? "" : " hidden") + ">" + list(d) + "</div>"; }).join("") + "</div>";

    // tablet / desktop: all days at once
    var wide = '<div class="hidden sm:grid gap-3 sm:grid-cols-2 xl:grid-cols-3">' + days.map(function (d) {
      return '<section class="card-soft p-4 ' + (d === today ? "is-today" : "") + '"><header class="flex items-center justify-between mb-3"><h4 class="font-display text-base">' + DAYS[d] + "</h4>" +
        (d === today ? '<span class="badge badge-primary badge-sm">اليوم</span>' : "") + "</header>" + list(d) + "</section>";
    }).join("") + "</div>";
    return phone + wide;
  };

  ui.payments = function (c) {
    if (!c.payments.length) return ui.empty("receipt", "لا توجد مدفوعات مسجّلة", "لم تُسجَّل أي دفعة لهذه الدورة. للاستفسار يرجى مراجعة قسم الحسابات في المركز.");
    var total = c.payments.reduce(function (a, p) { return a + p.amount; }, 0);
    var html = '<div class="flex items-center justify-between gap-4 p-4 rounded-box bg-primary/10 border border-primary/20 mb-4">' +
      '<div class="flex items-center gap-3"><div class="size-10 rounded-full bg-primary text-primary-content grid place-items-center"><i data-lucide="wallet" class="size-5"></i></div>' +
      '<div><p class="text-sm opacity-70">إجمالي المدفوع</p><p class="text-xl font-semibold num">' + money(total) + "</p></div></div>" +
      '<span class="text-sm opacity-70 num">' + c.payments.length + " إيصال</span></div>";
    html += '<ul class="divide-y divide-base-300 -mx-2">';
    c.payments.forEach(function (p) {
      html += '<li><button class="w-full min-h-16 px-2 py-3 flex items-center gap-3 text-start rounded-field active:bg-base-200 hover:bg-base-200/60 transition-colors" data-receipt="' + c.id + ":" + p.id + '" aria-label="عرض الإيصال رقم ' + p.id + '">' +
        '<div class="size-10 rounded-xl bg-base-200 grid place-items-center text-primary shrink-0"><i data-lucide="receipt" class="size-5"></i></div>' +
        '<div class="flex-1 min-w-0"><p class="font-medium">' + esc(p.type) + "</p>" +
        '<p class="text-sm opacity-70 truncate"><span class="num">#' + p.id + "</span> · " + fmtDate(p.date, { day: "numeric", month: "short", year: "numeric" }) + " · " + esc(p.source) + "</p></div>" +
        '<p class="font-semibold num whitespace-nowrap">' + money(p.amount) + "</p>" +
        '<i data-lucide="chevron-left" class="size-4 opacity-40 shrink-0"></i></button></li>';
    });
    return html + "</ul>";
  };

  ui.marks = function (c) {
    var any = c.marks.some(function (m) { return m.mark != null; });
    if (!any) return ui.empty("graduation-cap", "لم تُرصد الدرجات بعد", "ستظهر درجاتك هنا فور اعتمادها من إدارة المركز.");
    var sum = 0;
    var rows = c.marks.map(function (m) {
      var has = m.mark != null; if (has) sum += m.mark;
      return '<li class="py-3"><div class="flex items-baseline justify-between gap-3"><span class="font-medium">' + m.label + "</span>" +
        '<span class="num whitespace-nowrap"><b>' + (has ? m.mark : "—") + '</b> <span class="opacity-50 text-sm">/ ' + m.max + "</span></span></div>" +
        (has ? '<div class="h-1.5 rounded-full bg-base-200 mt-2 overflow-hidden"><div class="h-full rounded-full bg-primary" style="width:' + (m.mark / m.max * 100) + '%"></div></div>'
             : '<p class="text-xs opacity-50 mt-1">لم تُرصد بعد</p>') + "</li>";
    }).join("");
    var total = c.marks_published ? c.total_mark : sum;
    var html = "";
    if (c.marks_published) {
      html += '<div class="grid grid-cols-2 gap-3 mb-2">' +
        '<div class="rounded-box p-4 bg-base-200"><p class="text-sm opacity-70">المجموع</p><p class="text-2xl sm:text-3xl font-semibold num whitespace-nowrap">' + total + '<span class="text-sm sm:text-base opacity-50"> / 100</span></p></div>' +
        '<div class="rounded-box p-4 bg-accent/20 border border-accent/30"><p class="text-sm opacity-70">التقدير العام</p><p class="text-xl font-display mt-1 flex items-center gap-2"><i data-lucide="award" class="size-6 text-accent"></i>' + esc(c.final_grade) + "</p></div></div>";
    } else {
      html += '<div role="status" class="alert alert-soft alert-info mb-2 text-sm"><i data-lucide="info" class="size-5"></i><span>الدرجات قيد الرصد — المعتمد حتى الآن <b class="num">' + sum + "</b> من 100. تُتاح الشهادة بعد اعتماد النتيجة النهائية.</span></div>";
    }
    html += '<ul class="divide-y divide-base-300">' + rows + "</ul>" +
      '<div class="flex items-baseline justify-between border-t-2 border-base-300 pt-3 font-semibold"><span>المجموع</span><span class="num">' + total + " / 100</span></div>";
    html += '<div class="mt-5">' +
      (c.marks_published
        ? '<a class="btn btn-primary btn-lg sm:btn-md w-full sm:w-auto" href="#" data-toast="سيتم تنزيل الشهادة (PDF) — في المرحلة الثانية عبر رابط موقّع"><i data-lucide="file-down" class="size-5"></i>تنزيل الشهادة PDF</a>'
        : '<button class="btn btn-lg sm:btn-md w-full sm:w-auto" disabled><i data-lucide="lock" class="size-5"></i>الشهادة غير متاحة بعد</button>') + "</div>";
    return html;
  };

  ui.receiptModal = function (courseId, paymentId) {
    var c = courseById(courseId), p = c.payments.filter(function (x) { return String(x.id) === String(paymentId); })[0];
    if (!p) return;
    var dlg = document.getElementById("gh-receipt") || document.body.appendChild(Object.assign(document.createElement("dialog"), { id: "gh-receipt", className: "modal modal-bottom sm:modal-middle" }));
    dlg.innerHTML = '<div class="modal-box p-0 overflow-hidden"><div class="hero-band bg-pattern p-5 flex items-center gap-3"><img src="assets/img/logo-icon.svg" class="h-10 brightness-0 invert" alt=""><div><p class="text-xs opacity-80">جمعية الغدير لعلوم القرآن</p><h3 class="font-display text-xl">إيصال استلام نقدي</h3></div></div>' +
      '<dl class="kv grid grid-cols-2 gap-4 p-5"><div><dt>رقم الإيصال</dt><dd class="num">#' + p.id + "</dd></div><div><dt>التاريخ</dt><dd>" + fmtDate(p.date) + "</dd></div>" +
      '<div class="col-span-2"><dt>استلمنا من</dt><dd>' + esc(DEMO.student.name) + '</dd></div><div><dt>رقم الطالب</dt><dd class="num">' + DEMO.student.student_id + "</dd></div><div><dt>المبلغ</dt><dd class=\"num text-lg\">" + money(p.amount) + "</dd></div>" +
      '<div class="col-span-2"><dt>التفاصيل</dt><dd>' + esc(p.type) + " — " + esc(c.long_name) + "</dd></div></dl>" +
      '<div class="modal-action p-5 pt-0 mt-0"><form method="dialog" class="flex gap-2 w-full"><button class="btn btn-primary flex-1" type="button" onclick="window.print()"><i data-lucide="printer" class="size-4"></i>طباعة</button><button class="btn flex-1">إغلاق</button></form></div></div>' +
      '<form method="dialog" class="modal-backdrop"><button>إغلاق</button></form>';
    icons(); dlg.showModal();
  };

  /* ---------------------------------------------------------------- shell */
  var NAV = [
    { id: "home",       label: "الدورة الحالية", short: "الرئيسية",  icon: "book-open",       href: "home.html" },
    { id: "attendance", label: "سجل الحضور",     short: "الحضور",    icon: "calendar-check",  href: "attendance.html" },
    { id: "stats",      label: "إحصائيات الحضور", short: "إحصائيات",  icon: "chart-pie",       href: "stats.html" },
    { id: "archive",    label: "أرشيف الدورات",  short: "الأرشيف",   icon: "archive",         href: "archive.html" },
    { id: "profile",    label: "الملف الشخصي",   short: "حسابي",     icon: "user-round",      href: "profile.html" }
  ];

  function shell() {
    var body = document.body, page = body.getAttribute("data-page");
    if (!page || body.getAttribute("data-shell") === "none") return;
    var main = document.querySelector("main");
    var st = DEMO.student;
    var title = body.getAttribute("data-title") || "";
    var crumb = body.getAttribute("data-crumb") || "بوابة الطالب";

    var side = '<aside class="w-72 min-h-full bg-base-100 border-e border-base-300 flex flex-col">' +
      '<a href="home.html" class="px-6 pt-6 pb-4 block" aria-label="الرئيسية"><img src="assets/img/logo-full.svg" alt="جمعية الغدير لعلوم القرآن" class="h-14 w-auto"></a>' +
      '<div class="mx-4 mb-5 rounded-box hero-band bg-pattern p-4 flex items-center gap-3">' + ui.avatar("w-11 h-14") +
      '<div class="min-w-0"><p class="text-xs opacity-80">مرحبًا،</p><p class="font-semibold truncate">' + esc(st.first_name) + '</p><p class="text-xs opacity-80 num">رقم الطالب ' + st.student_id + "</p></div></div>" +
      '<ul class="menu w-full px-3 gap-1 text-[0.95rem]">' +
      NAV.map(function (n) {
        return '<li><a href="' + n.href + '" class="py-2.5 ' + (n.id === page ? "menu-active" : "") + '"' + (n.id === page ? ' aria-current="page"' : "") + '><i data-lucide="' + n.icon + '" class="size-5"></i>' + n.label + "</a></li>";
      }).join("") +
      '</ul><div class="mt-auto p-4 space-y-3"><div class="join w-full" role="group" aria-label="المظهر">' +
      theme.modes.map(function (m) { return '<button class="btn btn-sm join-item flex-1" data-theme-set="' + m + '"><i data-lucide="' + theme.icons[m] + '" class="size-4"></i><span class="sr-only">' + theme.labels[m] + "</span></button>"; }).join("") +
      '</div><a href="login.html" class="btn btn-ghost btn-block justify-start text-error"><i data-lucide="log-out" class="size-5"></i>تسجيل الخروج</a></div></aside>';

    var top = '<header class="sticky top-0 z-30 bg-base-100/90 backdrop-blur border-b border-base-300 pt-safe">' +
      '<div class="flex items-center gap-3 px-4 lg:px-8 h-14 lg:h-16 max-w-6xl mx-auto w-full">' +
      '<a href="home.html" class="lg:hidden shrink-0" aria-label="الرئيسية"><img src="assets/img/logo-icon.svg" alt="" class="h-9 w-auto"></a>' +
      '<div class="flex-1 min-w-0"><p class="text-xs opacity-60 leading-none mb-1">' + esc(crumb) + '</p><h1 class="font-display text-base sm:text-lg leading-tight truncate">' + esc(title) + "</h1></div>" +
      '<button class="btn btn-ghost btn-circle lg:hidden" data-theme-btn></button>' +
      '<a href="profile.html" class="lg:hidden" aria-label="الملف الشخصي">' + ui.avatar("w-8 h-10") + "</a></div></header>";

    var dock = '<nav class="dock dock-md bg-base-100/90 backdrop-blur border-t border-base-300 lg:hidden z-40" aria-label="التنقل الرئيسي">' +
      NAV.map(function (n) {
        var a = n.id === page;
        return '<a href="' + n.href + '" class="' + (a ? "dock-active text-primary" : "opacity-70") + '"' + (a ? ' aria-current="page"' : "") + '><i data-lucide="' + n.icon + '" class="size-5"></i><span class="dock-label">' + n.short + "</span></a>";
      }).join("") + "</nav>";

    var wrap = document.createElement("div");
    wrap.className = "drawer lg:drawer-open";
    wrap.innerHTML = '<input id="gh-drawer" type="checkbox" class="drawer-toggle" aria-hidden="true">' +
      '<div class="drawer-content flex flex-col min-h-dvh">' + top + '<div data-slot="main" class="flex-1"></div>' + dock + "</div>" +
      '<div class="drawer-side z-40"><label for="gh-drawer" class="drawer-overlay" aria-label="إغلاق القائمة"></label>' + side + "</div>";
    body.insertBefore(wrap, body.firstChild);
    main.classList.add("max-w-6xl", "mx-auto", "w-full", "px-4", "lg:px-8", "pt-4", "lg:pt-8", "pb-dock");
    wrap.querySelector('[data-slot="main"]').appendChild(main);
  }

  /* ------------------------------------------------- design-state switcher */
  function stateSwitcher() {
    var spec = document.body.getAttribute("data-states");
    if (!spec) return;
    var states = spec.split(",").map(function (s) { var p = s.split(":"); return { id: p[0], label: p[1] }; });
    var el = document.createElement("div");
    el.className = "demo-switch dropdown dropdown-top";
    el.innerHTML = '<div tabindex="0" role="button" class="btn btn-sm btn-neutral shadow-lg rounded-full" aria-label="حالات التصميم"><i data-lucide="sparkles" class="size-4"></i><span class="hidden sm:inline">حالات التصميم</span></div>' +
      '<ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box w-52 p-2 shadow-xl border border-base-300 mb-2">' +
      '<li class="menu-title text-xs">للمعاينة فقط — ليست جزءًا من المنتج</li>' +
      states.map(function (s) { return '<li><a data-set-state="' + s.id + '">' + s.label + "</a></li>"; }).join("") + "</ul>";
    document.body.appendChild(el);
    el.addEventListener("click", function (e) { var a = e.target.closest("[data-set-state]"); if (a) setState(a.getAttribute("data-set-state")); });
    setState(states[0].id);
  }
  function setState(id) {
    document.querySelectorAll("[data-state]").forEach(function (n) {
      n.hidden = n.getAttribute("data-state").split(" ").indexOf(id) === -1;
    });
    document.querySelectorAll("[data-set-state]").forEach(function (a) { a.classList.toggle("menu-active", a.getAttribute("data-set-state") === id); });
    document.dispatchEvent(new CustomEvent("gh:state", { detail: id }));
  }

  /* ---------------------------------------------------------------- toast */
  function toast(msg, kind) {
    var box = document.getElementById("gh-toast") || document.body.appendChild(Object.assign(document.createElement("div"), { id: "gh-toast", className: "toast toast-top toast-center z-[70]" }));
    var a = document.createElement("div");
    a.className = "alert alert-" + (kind || "success") + " shadow-lg";
    a.setAttribute("role", "status");
    a.innerHTML = '<i data-lucide="' + (kind === "error" ? "circle-x" : "circle-check") + '" class="size-5"></i><span>' + esc(msg) + "</span>";
    box.appendChild(a); icons();
    setTimeout(function () { a.remove(); }, 3200);
  }

  /* ----------------------------------------------------------------- boot */
  document.addEventListener("DOMContentLoaded", function () {
    shell();
    stateSwitcher();
    syncThemeControls();
    document.addEventListener("click", function (e) {
      var t;
      if ((t = e.target.closest("[data-theme-btn]"))) theme.cycle();
      else if ((t = e.target.closest("[data-theme-set]"))) theme.set(t.getAttribute("data-theme-set"));
      else if ((t = e.target.closest("[data-receipt]"))) { var p = t.getAttribute("data-receipt").split(":"); ui.receiptModal(p[0], p[1]); }
      else if ((t = e.target.closest("[data-toast]"))) { e.preventDefault(); toast(t.getAttribute("data-toast"), "info"); }
      else if ((t = e.target.closest("[data-tt-day]"))) {
        var box = t.closest("[data-tt]"), d = t.getAttribute("data-tt-day");
        box.querySelectorAll("[data-tt-day]").forEach(function (b) {
          var on = b === t; b.setAttribute("aria-selected", on);
          b.classList.toggle("bg-primary", on); b.classList.toggle("text-primary-content", on); b.classList.toggle("bg-base-200", !on);
        });
        box.querySelectorAll("[data-tt-panel]").forEach(function (p) { p.hidden = p.getAttribute("data-tt-panel") !== d; });
      }
    });
    icons();
  });

  window.GH = {
    DEMO: DEMO, STATES: STATES, STATE_ORDER: STATE_ORDER, DAYS: DAYS, DAYS_SHORT: DAYS_SHORT, TODAY: TODAY,
    theme: theme, ui: ui, icons: icons, toast: toast, setState: setState,
    chip: chip, pip: pip, esc: esc, parse: parse, iso: iso,
    fmtDate: fmtDate, fmtHijri: fmtHijri, fmtTime: fmtTime, money: money, pct: pct,
    sessionsFor: sessionsFor, summarize: summarize, nextSession: nextSession, courseById: courseById
  };
})();
