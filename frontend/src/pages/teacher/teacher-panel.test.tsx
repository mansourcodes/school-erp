import { screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import type { Roster, TeacherSession, TeacherToday } from '@/entities/class-session';
import type { ClassroomDetail, TeacherStats } from '@/entities/classroom';
import { renderApp } from '../../../test/render-app';
import { demoViewer, server } from '../../../test/server';

const teacher = { role: 'teacher' as const, id: 5, name: 'الشيخ أحمد الستري', first_name: 'الشيخ', email: 't@example.com', photo_url: null };
const room = { id: 912, number: '2-ب', name: 'قاعة الإمام الصادق' };

const session = (over: Partial<TeacherSession>): TeacherSession => ({
  code: '2026-09-25#16:00#912#11',
  classroom_id: 912,
  date: '2026-09-25',
  start: '16:00',
  end: '17:00',
  curriculum_id: 11,
  curriculum: 'تجويد القرآن الكريم',
  status: 'open',
  recorded: false,
  editable: true,
  counts: null,
  updated_at: null,
  classroom: room,
  ...over,
});

const today: TeacherToday = {
  date: '2026-09-25',
  course: 'المسار الحوزوي 2026',
  today: [session({})],
  pending: [session({ code: '2026-09-18#16:00#912#11', date: '2026-09-18', status: 'pending' })],
  upcoming: [session({ code: '2026-10-02#16:00#912#11', date: '2026-10-02', status: 'upcoming', editable: false })],
  tiles: { classrooms: 1, students: 2, rate: 93.5, pending: 1 },
};

const roster = (over: Partial<Roster['session']> = {}): Roster => ({
  session: { ...session({}), classroom: room, ...over },
  students: [
    { id: 1, name: 'حسين علي', student_id: '20240001', photo_url: null, state: null },
    { id: 2, name: 'محمد جعفر', student_id: '20240002', photo_url: null, state: null },
  ],
  previous_code: '2026-09-18#16:00#912#11',
  next_code: null,
});

function asTeacher() {
  server.use(http.get('*/api/v1/me', () => HttpResponse.json({ data: teacher })));
}

describe('teacher panel', () => {
  it('logs a teacher in with email and opens the teacher today screen', async () => {
    let body: unknown;
    server.use(
      http.post('*/api/v1/auth/login', async ({ request }) => {
        body = await request.json();
        asTeacher();
        return HttpResponse.json({ data: teacher });
      }),
      http.get('*/api/v1/teacher/today', () => HttpResponse.json({ data: today })),
    );
    const { router } = renderApp('/login');
    const user = userEvent.setup();

    await user.click(await screen.findByRole('tab', { name: 'مدرّس' }));
    expect(screen.queryByLabelText('الرقم الشخصي (CPR)')).not.toBeVisible();
    await user.type(screen.getByLabelText('البريد الإلكتروني'), 't@example.com');
    await user.type(screen.getByLabelText('كلمة المرور'), 'secret-pass');
    await user.click(screen.getByRole('button', { name: 'دخول' }));

    await waitFor(() => expect(router.state.location.pathname).toBe('/teacher'));
    expect(body).toEqual({ role: 'teacher', email: 't@example.com', password: 'secret-pass' });
    expect(await screen.findByText('حصص لم تُرصد')).toBeInTheDocument();
    expect(screen.getAllByRole('link', { name: /رصد/ }).length).toBeGreaterThan(0);
  });

  it('keeps each role in its own area', async () => {
    server.use(http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })));
    const { router } = renderApp('/teacher');

    await waitFor(() => expect(router.state.location.pathname).toBe('/'));
  });

  it('records a roster: all present, save once complete, with the first-save hint', async () => {
    asTeacher();
    let saved: unknown;
    server.use(
      http.get('*/api/v1/teacher/sessions/:code', () => HttpResponse.json({ data: roster() })),
      http.put('*/api/v1/teacher/sessions/:code', async ({ request, params }) => {
        saved = { code: params.code, body: await request.json() };
        return HttpResponse.json({ data: { updated_at: '2026-09-25T16:05:00+03:00', created: true } });
      }),
    );
    renderApp('/teacher/attendance/' + encodeURIComponent('2026-09-25#16:00#912#11'));
    const user = userEvent.setup();

    expect(await screen.findByText('0 من 2 تم تحديدهم')).toBeInTheDocument();
    const save = screen.getByRole('button', { name: 'حفظ' });
    expect(save).toBeDisabled();
    // Task screen: no dock, a back arrow.
    expect(screen.queryByRole('navigation', { name: 'التنقل الرئيسي' })).not.toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'رجوع' })).toBeInTheDocument();

    await user.click(screen.getByRole('button', { name: 'الكل حاضر' }));
    const second = screen.getByRole('group', { name: 'محمد جعفر' });
    await user.click(within(second).getByRole('button', { name: 'غائب بعذر' }));

    expect(screen.getByText('2 من 2 تم تحديدهم')).toBeInTheDocument();
    expect(screen.getByText('عند الحفظ يصل لكل طالب إشعار بحالته')).toBeInTheDocument();
    await user.click(save);

    expect(await screen.findByText('تم الحفظ وأُرسل إشعار لكل طالب بحالته')).toBeInTheDocument();
    expect(saved).toEqual({ code: '2026-09-25#16:00#912#11', body: { states: { 1: 'present', 2: 'absent_excused' } } });
  });

  it('undo restores the saved states', async () => {
    asTeacher();
    server.use(http.get('*/api/v1/teacher/sessions/:code', () => HttpResponse.json({ data: roster() })));
    renderApp('/teacher/attendance/' + encodeURIComponent('2026-09-25#16:00#912#11'));
    const user = userEvent.setup();

    await user.click(await screen.findByRole('button', { name: 'الكل غائب' }));
    expect(screen.getByText('2 من 2 تم تحديدهم')).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'تراجع عن التغييرات' }));
    expect(screen.getByText('0 من 2 تم تحديدهم')).toBeInTheDocument();
  });

  it('shows a later-day session read-only', async () => {
    asTeacher();
    server.use(
      http.get('*/api/v1/teacher/sessions/:code', () =>
        HttpResponse.json({ data: roster({ date: '2026-10-02', status: 'upcoming', editable: false }) }),
      ),
    );
    renderApp('/teacher/attendance/' + encodeURIComponent('2026-10-02#16:00#912#11'));

    expect(await screen.findByText(/هذه الحصة في يوم لاحق/)).toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'حفظ' })).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'الكل حاضر' })).not.toBeInTheDocument();
  });

  it('opens a student sheet with photo actions from the classroom', async () => {
    asTeacher();
    const detail: ClassroomDetail = {
      id: 912,
      number: '2-ب',
      name: 'قاعة الإمام الصادق',
      course: { id: 41, long_name: 'المسار الحوزوي 2026', start_date: '2026-09-04', end_date: '2026-12-25' },
      subjects: [{ id: 11, name: 'تجويد القرآن الكريم' }],
      slots: [{ curriculum_id: 11, day: 5, start: '16:00', end: '17:00' }],
      rate: 75,
      students: [{ id: 1, name: 'حسين علي', student_id: '20240001', photo_url: null, rate: 66.7, recorded: 3, absences: 1, late: 0 }],
      readonly: false,
    };
    server.use(
      http.get('*/api/v1/teacher/classrooms/:id', () => HttpResponse.json({ data: detail })),
      http.get('*/api/v1/teacher/classrooms/:id/sessions', () => HttpResponse.json({ data: [] })),
    );
    renderApp('/teacher/classes/912');
    const user = userEvent.setup();

    await user.click(await screen.findByRole('button', { name: /حسين علي/ }));
    const sheet = screen.getByRole('dialog', { name: 'حسين علي' });
    expect(within(sheet).getByRole('button', { name: 'التقاط صورة' })).toBeInTheDocument();
    expect(within(sheet).getByRole('button', { name: 'من المعرض' })).toBeInTheDocument();
    // No photo yet: nothing to delete.
    expect(within(sheet).queryByRole('button', { name: 'حذف الصورة' })).not.toBeInTheDocument();
    expect(within(sheet).getByText('66.7%')).toBeInTheDocument();
  });

  it('lists students who need follow-up on the stats screen', async () => {
    asTeacher();
    const stats: TeacherStats = {
      counts: { present: 3, late: 0, late_excused: 0, absent: 3, absent_excused: 0, unrecorded: 0 },
      recorded: 6,
      attended: 3,
      rate: 50,
      punctual: 50,
      students: [],
      follow_up: [
        { id: 2, name: 'محمد جعفر', student_id: '20240002', photo_url: null, rate: 0, recorded: 3, absences: 3, late: 0, classroom_id: 912, classroom: room },
      ],
      classrooms: [{ id: 912, label: room, rate: 50 }],
    };
    server.use(
      http.get('*/api/v1/teacher/classrooms', () => HttpResponse.json({ data: [] })),
      http.get('*/api/v1/teacher/stats', () => HttpResponse.json({ data: stats })),
    );
    renderApp('/teacher/stats');

    expect(await screen.findByRole('heading', { name: 'طلاب يحتاجون متابعة' })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /محمد جعفر/ })).toHaveAttribute('href', '/teacher/classes/912');
  });
});
