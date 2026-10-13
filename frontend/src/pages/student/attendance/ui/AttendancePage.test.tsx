import { screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import type { Session } from '@/entities/attendance';
import type { CourseDetail, CourseSummary } from '@/entities/course';
import { renderApp } from '../../../../../test/render-app';
import { demoViewer, server } from '../../../../../test/server';

const s = (date: string, curriculum_id: number, state: Session['state'], start = '16:00'): Session => ({
  date,
  start,
  end: null,
  curriculum_id,
  curriculum: curriculum_id === 1 ? 'تجويد القرآن الكريم' : 'الفقه',
  teacher: 'الشيخ أحمد',
  state,
  other_classroom: false,
});

// Fridays (tajweed) and Saturdays (fiqh), September–October 2026.
const sessions: Session[] = [
  s('2026-09-11', 1, 'present'),
  s('2026-09-12', 2, 'late'),
  s('2026-09-18', 1, 'absent'),
  s('2026-09-19', 2, 'present'),
  s('2026-10-02', 1, 'absent_excused'),
  s('2026-10-03', 2, 'unrecorded'),
];

const current = { id: 41, long_name: 'الدورة الحالية 2026', start_date: '2026-09-04', end_date: '2026-12-25' } as CourseDetail;
const archived = [{ id: 38, long_name: 'المستوى الأول 2025', start_date: '2025-09-05', end_date: '2025-12-26' } as CourseSummary];

function serve() {
  const requested: (string | null)[] = [];
  server.use(
    http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })),
    http.get('*/api/v1/student/courses/current', () => HttpResponse.json({ data: current })),
    http.get('*/api/v1/student/courses', () => HttpResponse.json({ data: archived })),
    http.get('*/api/v1/student/attendance', ({ request }) => {
      const courseId = new URL(request.url).searchParams.get('course_id');
      requested.push(courseId);
      return HttpResponse.json({ data: courseId === '38' ? [s('2025-09-12', 1, 'absent')] : sessions });
    }),
  );
  return requested;
}

describe('AttendancePage', () => {
  it('shows the current course summary and opens a day in a bottom sheet', async () => {
    const requested = serve();
    renderApp('/attendance');
    const user = userEvent.setup();

    // 3 attended (present, late, present) of 5 recorded; the unrecorded one is excluded.
    expect(await screen.findByText('3 من 5 حصة')).toBeInTheDocument();
    expect(screen.getByRole('progressbar', { name: 'نسبة الحضور' })).toHaveTextContent('60%');
    expect(screen.getByRole('combobox', { name: 'الدورة' })).toHaveValue('41');
    expect(requested).toEqual(['41']);

    // The calendar opens on the month of the latest session.
    expect(screen.getByRole('heading', { level: 2, name: /أكتوبر/ })).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: /الجمعة، 2 أكتوبر، 1 حصص/ }));

    const sheet = screen.getByRole('dialog');
    expect(within(sheet).getByText('تجويد القرآن الكريم')).toBeInTheDocument();
    expect(within(sheet).getByText('غائب بعذر')).toBeInTheDocument();
  });

  it('filters the summary by subject', async () => {
    serve();
    renderApp('/attendance');
    const user = userEvent.setup();

    await user.click(await screen.findByRole('button', { name: 'الفقه' }));

    // Fiqh: late + present attended, one unrecorded.
    expect(screen.getByText('2 من 2 حصة')).toBeInTheDocument();
  });

  it('lists sessions by week, newest first, with a status filter', async () => {
    serve();
    renderApp('/attendance');
    const user = userEvent.setup();

    await user.click(await screen.findByRole('button', { name: 'قائمة' }));
    const weeks = screen.getAllByRole('heading', { level: 3 });
    expect(weeks[0]).toHaveTextContent('أسبوع 27 سبتمبر');
    expect(weeks).toHaveLength(3);

    const filters = screen.getByRole('group', { name: 'تصفية حسب الحالة' });
    for (const label of ['حاضر', 'متأخر', 'متأخر بعذر', 'غائب بعذر', 'لم يُرصد']) {
      await user.click(within(filters).getByRole('button', { name: label }));
    }
    // Only absences remain: one session on 18 September.
    expect(screen.getAllByRole('heading', { level: 3 })).toHaveLength(1);
    expect(screen.getByText(/الجمعة، 18 سبتمبر/)).toBeInTheDocument();
  });

  it('shows another course from ?course= and switches with the picker', async () => {
    const requested = serve();
    renderApp('/attendance?course=38');
    const user = userEvent.setup();

    expect(await screen.findByText('0 من 1 حصة')).toBeInTheDocument();
    expect(screen.getByRole('combobox', { name: 'الدورة' })).toHaveValue('38');

    await user.selectOptions(screen.getByRole('combobox', { name: 'الدورة' }), '41');
    expect(await screen.findByText('3 من 5 حصة')).toBeInTheDocument();
    expect(requested).toEqual(['38', '41']);
  });

  it('opens the day from a notification link (?date=)', async () => {
    serve();
    renderApp('/attendance?date=2026-09-18');

    const sheet = await screen.findByRole('dialog');
    expect(within(sheet).getByRole('heading', { name: /الجمعة، 18 سبتمبر/ })).toBeInTheDocument();
    expect(within(sheet).getByText('غائب')).toBeInTheDocument();
  });

  it('explains when nothing was recorded yet', async () => {
    serve();
    server.use(http.get('*/api/v1/student/attendance', () => HttpResponse.json({ data: [] })));
    renderApp('/attendance');

    expect(await screen.findByRole('heading', { name: 'لا توجد سجلات حضور بعد' })).toBeInTheDocument();
  });
});
