import { screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import type { Roster, TeacherSession, TeacherToday } from '@/entities/class-session';
import type { ClassroomDetail, TeacherStats } from '@/entities/classroom';
import { renderApp } from '../../../test/render-app';
import { demoViewer, server } from '../../../test/server';

const teacher = { role: 'teacher' as const, id: 5, name: 'الشيخ أحمد الستري', first_name: 'الشيخ', username: 'ahmad', email: 't@example.com', photo_url: null };
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
  it('logs a teacher in with their username and opens the teacher today screen', async () => {
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
    await user.type(screen.getByLabelText('اسم المستخدم'), ' ahmad ');
    await user.type(screen.getByLabelText('كلمة المرور'), 'secret-pass');
    await user.click(screen.getByRole('button', { name: 'دخول' }));

    await waitFor(() => expect(router.state.location.pathname).toBe('/teacher'));
    expect(body).toEqual({ role: 'teacher', username: 'ahmad', password: 'secret-pass' });
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
        return HttpResponse.json({ data: { updated_at: '2026-09-25T16:05:00+03:00', created: true, push_pending: 0 } });
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

    expect(await screen.findByText('تم حفظ الحضور')).toBeInTheDocument();
    expect(saved).toEqual({ code: '2026-09-25#16:00#912#11', body: { states: { 1: 'present', 2: 'absent_excused' } } });
  });

  it('after a first save, opens the sending page and sends each notification one by one', async () => {
    asTeacher();
    const sentIds: number[] = [];
    let saved = false;
    server.use(
      http.get('*/api/v1/teacher/sessions/:code', () => HttpResponse.json({ data: roster() })),
      http.put('*/api/v1/teacher/sessions/:code', () => {
        saved = true;
        return HttpResponse.json({ data: { updated_at: '2026-09-25T16:05:00+03:00', created: true, push_pending: 2 } });
      }),
      http.get('*/api/v1/teacher/push-deliveries', () =>
        HttpResponse.json({
          data: saved
            ? [
                { id: 7, type: 'attendance', student_name: 'علي حسن' },
                { id: 8, type: 'attendance', student_name: 'محمد جعفر' },
              ].filter((row) => !sentIds.includes(row.id))
            : [],
        }),
      ),
      http.post('*/api/v1/teacher/push-deliveries/:id/send', ({ params }) => {
        sentIds.push(Number(params.id));
        return HttpResponse.json({ data: { id: Number(params.id), status: 'sent' } });
      }),
    );
    const { router } = renderApp('/teacher/attendance/' + encodeURIComponent('2026-09-25#16:00#912#11'));
    const user = userEvent.setup();

    await user.click(await screen.findByRole('button', { name: 'الكل حاضر' }));
    await user.click(screen.getByRole('button', { name: 'حفظ' }));

    expect(await screen.findByText('تم حفظ الحضور')).toBeInTheDocument();
    expect(router.state.location.pathname).toBe('/teacher/notify');
    expect(await screen.findByText('أُرسلت الإشعارات')).toBeInTheDocument();
    expect(screen.getByText('2 / 2')).toBeInTheDocument();
    expect(sentIds).toEqual([7, 8]);

    await user.click(screen.getByRole('button', { name: 'متابعة' }));
    expect(router.state.location.pathname).toBe('/teacher');
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

  it("uploads the teacher's own photo from the profile and shows it", async () => {
    let photoUrl: string | null = null;
    let uploads = 0;
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: { ...teacher, photo_url: photoUrl } })),
      http.get('*/api/v1/teacher/classrooms', () => HttpResponse.json({ data: [] })),
      http.post('*/api/v1/teacher/photo', () => {
        uploads++;
        photoUrl = '/storage/teachers/photos/5/a.webp';
        return HttpResponse.json({ data: { photo_url: photoUrl } });
      }),
    );
    renderApp('/teacher/profile');
    const user = userEvent.setup();

    expect(await screen.findByRole('button', { name: 'إضافة صورة' })).toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'حذف الصورة' })).not.toBeInTheDocument();

    await user.upload(screen.getByTestId('teacher-photo-input'), new File(['x'], 'me.jpg', { type: 'image/jpeg' }));

    expect(await screen.findByText('تم تحديث الصورة')).toBeInTheDocument();
    expect(uploads).toBe(1);
    // /me is refetched: the avatar shows the photo and the photo can now be changed or deleted.
    expect(await screen.findByRole('button', { name: 'تغيير الصورة' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'حذف الصورة' })).toBeInTheDocument();
    expect(screen.getAllByRole('img', { name: teacher.name })[0]).toHaveAttribute('src', '/storage/teachers/photos/5/a.webp');
  });

  it('changes the password from the profile after confirming the current one', async () => {
    asTeacher();
    const bodies: unknown[] = [];
    server.use(
      http.put('*/api/v1/teacher/password', async ({ request }) => {
        const body = (await request.json()) as { current_password: string };
        bodies.push(body);
        return body.current_password === 'old-secret'
          ? new HttpResponse(null, { status: 204 })
          : HttpResponse.json(
              { message: 'invalid', errors: { current_password: ['كلمة المرور الحالية غير صحيحة.'] } },
              { status: 422 },
            );
      }),
    );
    renderApp('/teacher/profile');
    const user = userEvent.setup();

    const current = await screen.findByLabelText('كلمة المرور الحالية');
    const next = screen.getByLabelText('كلمة المرور الجديدة');
    const confirm = screen.getByLabelText('تأكيد كلمة المرور الجديدة');
    const submit = screen.getByRole('button', { name: 'حفظ كلمة المرور' });

    // Client checks first: nothing is sent while the confirmation differs.
    await user.type(current, 'wrong');
    await user.type(next, 'new-secret-1');
    await user.type(confirm, 'new-secret-2');
    await user.click(submit);
    expect(await screen.findByText('تأكيد كلمة المرور غير مطابق.')).toBeInTheDocument();
    expect(bodies).toHaveLength(0);

    // The server rejects a wrong current password on its field.
    await user.clear(confirm);
    await user.type(confirm, 'new-secret-1');
    await user.click(submit);
    expect(await screen.findByText('كلمة المرور الحالية غير صحيحة.')).toBeInTheDocument();

    await user.clear(current);
    await user.type(current, 'old-secret');
    await user.click(submit);
    expect(await screen.findByText('تم تغيير كلمة المرور')).toBeInTheDocument();
    expect(bodies.at(-1)).toEqual({ current_password: 'old-secret', password: 'new-secret-1', password_confirmation: 'new-secret-1' });
    expect(screen.getByLabelText('كلمة المرور الحالية')).toHaveValue('');
  });

  it('changes the username from the profile and shows the new one', async () => {
    asTeacher();
    const bodies: unknown[] = [];
    server.use(
      http.put('*/api/v1/teacher/username', async ({ request }) => {
        const body = (await request.json()) as { username: string; current_password: string };
        bodies.push(body);
        if (body.current_password !== 'secret-pass') {
          return HttpResponse.json(
            { message: 'invalid', errors: { current_password: ['كلمة المرور الحالية غير صحيحة.'] } },
            { status: 422 },
          );
        }
        return body.username === 'taken'
          ? HttpResponse.json(
              { message: 'invalid', errors: { username: ['اسم المستخدم مستخدم من قبل، اختر اسمًا آخر.'] } },
              { status: 422 },
            )
          : HttpResponse.json({ data: { ...teacher, username: body.username.toLowerCase() } });
      }),
    );
    renderApp('/teacher/profile');
    const user = userEvent.setup();

    const input = await screen.findByLabelText('اسم المستخدم الجديد');
    const password = screen.getByLabelText('كلمة المرور الحالية للتأكيد');
    const submit = screen.getByRole('button', { name: 'حفظ اسم المستخدم' });
    expect(input).toHaveValue('ahmad');
    expect(submit).toBeDisabled();

    // Client checks first: nothing is sent for a malformed name or without the password.
    await user.clear(input);
    await user.type(input, 'أحمد');
    await user.click(submit);
    expect(await screen.findByText('يمكن أن يحتوي اسم المستخدم على أحرف إنجليزية وأرقام و . _ - فقط.')).toBeInTheDocument();
    expect(screen.getByText('أدخل كلمة المرور الحالية.')).toBeInTheDocument();
    expect(bodies).toHaveLength(0);

    // The server rejects a wrong password, then a taken name, on their fields.
    await user.clear(input);
    await user.type(input, 'taken');
    await user.type(password, 'wrong');
    await user.click(submit);
    expect(await screen.findByText('كلمة المرور الحالية غير صحيحة.')).toBeInTheDocument();
    await user.clear(password);
    await user.type(password, 'secret-pass');
    await user.click(submit);
    expect(await screen.findByText('اسم المستخدم مستخدم من قبل، اختر اسمًا آخر.')).toBeInTheDocument();

    await user.clear(input);
    await user.type(input, 'Ahmad.Sitri');
    await user.click(submit);
    expect(await screen.findByText('تم تغيير اسم المستخدم')).toBeInTheDocument();
    expect(bodies.at(-1)).toEqual({ username: 'Ahmad.Sitri', current_password: 'secret-pass' });
    expect(input).toHaveValue('ahmad.sitri');
    expect(password).toHaveValue('');
    expect(screen.getAllByText('ahmad.sitri').length).toBeGreaterThan(0);
  });
  it('shows the admin banner and hides the account forms while an admin views as the teacher', async () => {
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: { ...teacher, impersonated_by: { name: 'المسؤول' } } })),
      http.get('*/api/v1/teacher/classrooms', () => HttpResponse.json({ data: [] })),
    );
    renderApp('/teacher/profile');

    expect(await screen.findByText(`تتصفّح بوابة ${teacher.name} بصفتك المسؤول المسؤول`)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'إنهاء' })).toBeInTheDocument();
    expect(screen.queryByTestId('teacher-photo-input')).not.toBeInTheDocument();
    expect(screen.queryByText('تغيير كلمة المرور')).not.toBeInTheDocument();
  });
});
