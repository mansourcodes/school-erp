/* ==========================================================================
   Al-Ghadeer — Teacher panel mockup data + components (Phase 3 design)
   --------------------------------------------------------------------------
   Load after app.js. Sets GH.shellConfig so the shared shell renders the
   teacher navigation. Data shapes match PLAN.md §8 (teacher API).
   Rules shown in the design:
     • current course (active + latest + one)  → read + edit
     • archived courses (is_active = 0)        → read only
     • only classrooms where ClassRoom.teachers[].user_id = me
   ========================================================================== */
(function () {
  "use strict";
  var GH = window.GH, esc = GH.esc, TODAY = GH.TODAY;
  var NOW_MIN = 17 * 60 + 40;                       // demo "now": Mon 12 Oct 2026, 5:40 م

  function rng(a) { return function () { a |= 0; a = a + 0x6D2B79F5 | 0; var t = Math.imul(a ^ a >>> 15, 1 | a); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; }; }
  function mins(hm) { var p = hm.split(":"); return +p[0] * 60 + +p[1]; }

  /* ------------------------------------------------------------ people */
  var FIRST = ["محمد", "علي", "حسن", "حسين", "جعفر", "أحمد", "مهدي", "عباس", "محمود", "يوسف", "صادق", "باقر", "كاظم", "رضا", "مرتضى", "سجاد", "منتظر", "عيسى", "إبراهيم", "هادي", "زهير", "فاضل", "حمزة", "قاسم"];
  var LAST = ["الستري", "المرزوق", "الحايكي", "العريبي", "الجمري", "المحروس", "الشهابي", "النشابة", "الخياط", "العصفور", "البحراني", "الدرازي", "السماك", "القصاب", "المؤمن", "الحداد", "الشويخ", "العكري"];
  function roster(n, seed) {
    var r = rng(seed * 7919), out = [];
    for (var i = 0; i < n; i++) {
      var f = FIRST[Math.floor(r() * FIRST.length)], l = LAST[Math.floor(r() * LAST.length)];
      var id = 300 + seed * 40 + i;
      out.push({ id: id, student_id: "2025" + String(id).padStart(4, "0"), name: f + " " + FIRST[Math.floor(r() * FIRST.length)] + " " + l,
                 initial: f.charAt(0), has_photo: r() > 0.35, bias: 0.72 + r() * 0.26 });
    }
    return out.sort(function (a, b) { return a.name.localeCompare(b.name, "ar"); });
  }

  var teacher = { id: 5, name: "الشيخ أحمد الستري", first_name: "الشيخ أحمد", initial: "أ", email: "ahmed.alsitri@example.com", mobile: "39001122", photo: false };

  /* -------------------------------------------------------- classrooms */
  var D = GH.DEMO;
  function room(o) { o.students = roster(o.size, o.seed); return o; }
  var current = [
    room({ id: 915, number: "2-أ", place: "قاعة الإمام الباقر", course: D.current, curriculum: { id: 11, name: "تجويد القرآن الكريم" },
           slots: [{ day: 1, start: "17:15", end: "18:15" }, { day: 3, start: "17:15", end: "18:15" }], size: 16, seed: 2 }),
    room({ id: 920, number: "1-ج", place: "القاعة متعددة الأغراض", course: D.current, curriculum: { id: 21, name: "التجويد — المستوى الأول" },
           slots: [{ day: 1, start: "18:30", end: "19:30" }, { day: 4, start: "16:00", end: "17:00" }], size: 20, seed: 3 }),
    room({ id: 912, number: "2-ب", place: "قاعة الإمام الصادق — المبنى الرئيسي", course: D.current, curriculum: { id: 11, name: "تجويد القرآن الكريم" },
           slots: [{ day: 0, start: "16:00", end: "17:00" }, { day: 2, start: "16:00", end: "17:00" }], size: 18, seed: 1 })
  ];
  var archived = [
    room({ id: 870, number: "1-أ", place: "قاعة الإمام الباقر", course: D.archived[0], curriculum: { id: 1, name: "التجويد — المستوى الأول" },
           slots: [{ day: 0, start: "16:00", end: "17:00" }, { day: 2, start: "16:00", end: "17:00" }], size: 17, seed: 4, readonly: true }),
    room({ id: 801, number: "1-ج", place: "قاعة الإمام الباقر", course: D.archived[1], curriculum: { id: 1, name: "التجويد — المستوى الأول" },
           slots: [{ day: 0, start: "16:00", end: "17:00" }], size: 14, seed: 5, readonly: true })
  ];
  current.concat(archived).forEach(function (c) { c.readonly = !!c.readonly; });
  function roomById(id) { return current.concat(archived).filter(function (c) { return String(c.id) === String(id); })[0] || current[0]; }

  /* ---------------------------------------------------------- sessions */
  // code mirrors the Attends invariant: date#time#classroom#curriculum
  function code(c, date, start) { return date + "#" + start + "#" + c.id + "#" + c.curriculum.id; }
  var UNRECORDED = { "920": "2026-10-08" };          // one past session left unrecorded → nudge
  var SAVE_KEY = "gh-t-saved";
  function saved() { try { return JSON.parse(sessionStorage.getItem(SAVE_KEY) || "{}"); } catch (e) { return {}; } }
  function persist(map) { try { sessionStorage.setItem(SAVE_KEY, JSON.stringify(map)); } catch (e) { /* private mode */ } }

  var _cache = {};
  function sessionsFor(c) {
    if (_cache[c.id]) return _cache[c.id];
    var out = [], d = GH.parse(c.course.start_date), end = GH.parse(c.course.end_date), today = GH.iso(TODAY);
    for (; d <= end; d.setDate(d.getDate() + 1)) {
      c.slots.forEach(function (sl) {
        if (sl.day !== d.getDay()) return;
        var date = GH.iso(d), when;
        if (date < today) when = "past";
        else if (date > today) when = "future";
        else when = NOW_MIN >= mins(sl.end) ? "past" : NOW_MIN >= mins(sl.start) ? "now" : "later";
        out.push({ code: code(c, date, sl.start), classroom_id: c.id, date: date, start: sl.start, end: sl.end, when: when });
      });
    }
    return (_cache[c.id] = out);
  }
  function recordsFor(c, s) {
    var sv = saved()[s.code];
    if (sv) return { states: sv.states, recorded: true, updated_at: sv.updated_at, edited: true };
    if (s.when === "future" || s.when === "later" || s.when === "now" || UNRECORDED[c.id] === s.date) return { states: {}, recorded: false };
    var states = {}, idx = sessionsFor(c).indexOf(s);
    c.students.forEach(function (st, i) {
      var r = rng(c.id * 1000 + idx * 31 + i)(), b = st.bias, v;
      if (r < b) v = "present"; else if (r < b + 0.07) v = "late"; else if (r < b + 0.1) v = "late_excused";
      else if (r < b + 0.16) v = "absent_excused"; else v = "absent";
      states[st.id] = v;
    });
    return { states: states, recorded: true, updated_at: s.date + " " + s.end };
  }
  function saveRecords(s, states) {
    var m = saved(); m[s.code] = { states: states, updated_at: GH.iso(TODAY) + " " + "17:42" }; persist(m);
  }
  function bySession(codeStr) {
    var parts = codeStr.split("#"), c = roomById(parts[2]);
    return { room: c, session: sessionsFor(c).filter(function (s) { return s.code === codeStr; })[0] };
  }
  function count(states) {
    var o = { present: 0, late: 0, late_excused: 0, absent: 0, absent_excused: 0, unset: 0 };
    Object.keys(states).forEach(function (k) { o[states[k]] = (o[states[k]] || 0) + 1; });
    return o;
  }
  function roomStats(c) {
    var per = {}; c.students.forEach(function (st) { per[st.id] = { student: st, list: [] }; });
    var recorded = 0, pending = [];
    sessionsFor(c).forEach(function (s) {
      if (s.when === "future" || s.when === "later") return;
      var r = recordsFor(c, s);
      if (!r.recorded) { pending.push(s); return; }
      recorded++;
      c.students.forEach(function (st) { if (r.states[st.id]) per[st.id].list.push({ date: s.date, start: s.start, end: s.end, state: r.states[st.id] }); });
    });
    var students = c.students.map(function (st) { var x = GH.summarize(per[st.id].list); x.student = st; return x; });
    var all = []; Object.keys(per).forEach(function (k) { all = all.concat(per[k].list); });
    var sum = GH.summarize(all); sum.sessions = recorded; sum.pending = pending; sum.students = students;
    return sum;
  }
  function todaySessions() {
    var t = GH.iso(TODAY), out = [];
    current.forEach(function (c) { sessionsFor(c).forEach(function (s) { if (s.date === t) out.push({ room: c, session: s }); }); });
    return out.sort(function (a, b) { return a.session.start < b.session.start ? -1 : 1; });
  }
  function pendingAll() {
    var out = [];
    current.forEach(function (c) { sessionsFor(c).forEach(function (s) { if (s.when === "past" && !recordsFor(c, s).recorded) out.push({ room: c, session: s }); }); });
    return out;
  }

  /* -------------------------------------------------------------- UI */
  var ui = {};
  // student / teacher photo in the arch frame. No real photos in the mockup:
  // has_photo → neutral silhouette "photo", otherwise a dashed empty arch.
  var SIL = "data:image/svg+xml," + encodeURIComponent("<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 52'><rect width='40' height='52' fill='#cfe3df'/><circle cx='20' cy='20' r='8' fill='#8fb5ae'/><path d='M5 52c1-11 7-17 15-17s14 6 15 17z' fill='#8fb5ae'/></svg>");
  ui.photo = function (p, size, opts) {
    opts = opts || {};
    var sz = size || "w-10 h-12";
    if (p.has_photo || p.photo_url) {
      return '<div class="arch ' + sz + (opts.shrink === false ? "" : " shrink-0") + ' overflow-hidden bg-base-200" data-photo="' + p.id + '"><img src="' + (p.photo_url || SIL) + '" alt="" class="w-full h-full object-cover"></div>';
    }
    return '<div class="arch ' + sz + (opts.shrink === false ? "" : " shrink-0") + ' grid place-items-center bg-base-200 text-base-content/50" data-photo="' + p.id + '" title="بدون صورة"><span class="font-display ' + (opts.text || "text-sm") + '">' + esc(p.initial) + "</span></div>";
  };
  ui.teacherAvatar = function (size, txt) {
    return '<div class="arch arch-ring ' + (size || "w-11 h-14") + ' shrink-0"><div class="arch w-full h-full grid place-items-center bg-primary text-primary-content"><span class="font-display ' + (txt || "text-lg") + '">' + teacher.initial + "</span></div></div>";
  };
  ui.whenLabel = function (s) {
    if (s.date === GH.iso(TODAY)) return "اليوم";
    var d = GH.parse(s.date), t = new Date(TODAY); t.setDate(t.getDate() - 1);
    if (GH.iso(t) === s.date) return "أمس";
    return GH.fmtDate(s.date, { weekday: "long", day: "numeric", month: "short" });
  };
  // session card used on Today + classroom sessions list
  ui.sessionCard = function (c, s, opts) {
    opts = opts || {};
    var r = recordsFor(c, s), cnt = count(r.states), total = c.students.length;
    var href = "t-attendance.html?code=" + encodeURIComponent(s.code) + (opts.from ? "&from=" + encodeURIComponent(opts.from) : "");
    var badge, action, ring = "", primary = false;
    if (c.readonly) {
      badge = '<span class="badge badge-ghost badge-sm gap-1"><i data-lucide="lock" class="size-3"></i>للاطلاع</span>';
      action = '<a class="btn btn-sm btn-ghost" href="' + href + '">عرض</a>';
    } else if (s.when === "now" && r.recorded) {
      ring = " is-today";
      badge = '<span class="badge badge-success badge-soft badge-sm gap-1"><i data-lucide="check" class="size-3"></i>جارية · تم الرصد</span>';
      action = '<a class="btn btn-sm btn-ghost text-primary" href="' + href + '"><i data-lucide="square-pen" class="size-4"></i>تعديل</a>';
    } else if (s.when === "now") {
      ring = " is-today";
      badge = '<span class="badge badge-primary badge-sm gap-1"><span class="status status-success animate-pulse"></span>جارية الآن</span>';
      action = '<a class="btn btn-primary btn-block" href="' + href + '"><i data-lucide="calendar-check" class="size-4"></i>رصد الحضور</a>'; primary = true;
    } else if (s.when === "later" || s.when === "future") {
      badge = '<span class="badge badge-ghost badge-sm">قادمة</span>';
      action = '<a class="btn btn-sm btn-ghost" href="' + href + '">القائمة</a>';
    } else if (!r.recorded) {
      badge = '<span class="badge badge-warning badge-sm gap-1"><i data-lucide="triangle-alert" class="size-3"></i>لم تُرصد</span>';
      action = '<a class="btn btn-warning btn-block" href="' + href + '"><i data-lucide="calendar-check" class="size-4"></i>رصد الآن</a>'; primary = true;
    } else {
      badge = '<span class="badge badge-success badge-soft badge-sm gap-1"><i data-lucide="check" class="size-3"></i>تم الرصد</span>';
      action = '<a class="btn btn-sm btn-ghost text-primary" href="' + href + '"><i data-lucide="square-pen" class="size-4"></i>تعديل</a>';
    }
    var tally = r.recorded
      ? '<p class="text-xs opacity-70 mt-1 flex flex-wrap gap-x-2"><span>حاضر <b class="num">' + (cnt.present + cnt.late + cnt.late_excused) + "</b>/" + total + "</span><span>غياب <b class=\"num\">" + (cnt.absent + cnt.absent_excused) + "</b></span></p>"
      : '<p class="text-xs opacity-70 mt-1"><span class="num">' + total + "</span> طالب</p>";
    return '<article class="card-soft p-3.5' + ring + '"><div class="flex items-center gap-3">' +
      '<div class="rounded-field bg-base-200 w-16 py-2 text-center leading-tight shrink-0"><p class="text-[11px] opacity-70">' + (opts.dateLabel ? ui.whenLabel(s) : "") + '</p><p class="font-bold num text-sm">' + GH.fmtTime(s.start) + "</p></div>" +
      '<div class="min-w-0 flex-1"><div class="flex items-center gap-x-2 gap-y-1 flex-wrap"><p class="font-semibold">الصف <span class="num whitespace-nowrap">' + esc(c.number) + "</span></p>" + badge + "</div>" +
      '<p class="text-sm opacity-80 truncate">' + esc(c.curriculum.name) + "</p>" + tally + "</div>" +
      (primary ? "" : '<div class="shrink-0">' + action + "</div>") + "</div>" +
      (primary ? '<div class="mt-3">' + action + "</div>" : "") + "</article>";
  };

  /* ------------------------------------------------------------ shell */
  GH.shellConfig = {
    home: "t-home.html", profile: "t-profile.html", crumb: "لوحة المدرّس",
    avatar: function (size, txt) { return ui.teacherAvatar(size, txt); },
    person: { first_name: teacher.first_name, sub: "مدرّس · " + current.length + " صفوف" },
    nav: [
      { id: "t-home",      label: "اليوم",            short: "اليوم",     icon: "calendar-clock", href: "t-home.html", match: ["t-attendance"] },
      { id: "t-classes",   label: "صفوفي",            short: "صفوفي",     icon: "school",         href: "t-classes.html", match: ["t-classroom"] },
      { id: "t-stats",     label: "إحصائيات الصفوف",  short: "إحصائيات",  icon: "chart-pie",      href: "t-stats.html" },
      { id: "t-profile",   label: "حسابي",            short: "حسابي",     icon: "user-round",     href: "t-profile.html" }
    ]
  };

  GH.T = {
    teacher: teacher, current: current, archived: archived, roomById: roomById, NOW_MIN: NOW_MIN,
    sessionsFor: sessionsFor, recordsFor: recordsFor, saveRecords: saveRecords, bySession: bySession,
    count: count, roomStats: roomStats, todaySessions: todaySessions, pendingAll: pendingAll, ui: ui
  };
})();
