import { screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import type { CourseDetail, CourseSummary } from '@/entities/course';
import { renderApp } from '../../../../../test/render-app';
import { demoViewer, server } from '../../../../../test/server';

const summary = (id: number, year: string, name: string, grade: string | null): CourseSummary => ({
  id,
  classroom_id: id * 10,
  status: 'archived',
  long_name: name,
  academic_path: 'المسار الحوزوي',
  year,
  hijri_year: '1447',
  semester: 'الفصل الثاني',
  start_date: `${year}-02-01`,
  end_date: `${year}-05-28`,
  class_room_number: '1-أ',
  marks_published: grade !== null,
  total_mark: grade ? 91.5 : null,
  final_grade: grade,
});

const courses = [
  summary(38, '2026', 'المستوى الأول 2026', 'ممتاز'),
  summary(35, '2025', 'المستوى التمهيدي 2025', 'جيد جدًا'),
  summary(31, '2025', 'دورة الصيف 2025', null),
];

const detail = (id: number): CourseDetail => ({
  ...courses.find((c) => c.id === id)!,
  duration: '64',
  class_room_name: 'قاعة الإمام الباقر',
  curricula: [{ id: 1, name: `منهج الدورة ${id}`, teacher: 'الشيخ أحمد', slots: [{ day: 5, start: '16:00', end: '17:00' }] }],
  payments: [],
  marks: [{ key: 'm0', label: 'الامتحان النهائي', max: 100, mark: 91.5 }],
});

function serve(list: CourseSummary[] = courses) {
  const detailCalls: number[] = [];
  server.use(
    http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })),
    http.get('*/api/v1/student/courses', () => HttpResponse.json({ data: list })),
    http.get('*/api/v1/student/courses/:id', ({ params }) => {
      detailCalls.push(Number(params.id));
      return HttpResponse.json({ data: detail(Number(params.id)) });
    }),
  );
  return detailCalls;
}

const card = (name: string) => screen.getByRole('heading', { name }).closest('article') as HTMLElement;

describe('ArchivePage', () => {
  it('lists archived courses and opens the newest one with its result first', async () => {
    const calls = serve();
    renderApp('/archive');

    await screen.findByRole('heading', { name: 'المستوى الأول 2026' });
    const newest = card('المستوى الأول 2026');
    expect(within(newest).getByText('ممتاز · 91.5 / 100')).toBeInTheDocument();
    expect(await within(newest).findByRole('tab', { name: 'الدرجات', selected: true })).toBeInTheDocument();
    expect(within(card('دورة الصيف 2025')).getByText('لم تُعتمد النتيجة')).toBeInTheDocument();
    // Only the opened card fetched its detail.
    expect(calls).toEqual([38]);
  });

  it('fetches a course detail only when its card is opened', async () => {
    const calls = serve();
    renderApp('/archive');
    const user = userEvent.setup();

    await screen.findByRole('heading', { name: 'المستوى التمهيدي 2025' });
    await user.click(within(card('المستوى التمهيدي 2025')).getByRole('button', { expanded: false }));
    await user.click(await within(card('المستوى التمهيدي 2025')).findByRole('tab', { name: 'الجدول' }));

    // Phone and desktop timetable layouts are both in the DOM (CSS picks one).
    expect((await screen.findAllByText('منهج الدورة 35')).length).toBeGreaterThan(0);
    expect(calls).toEqual([38, 35]);
  });

  it('opens the linked course from /archive/:courseId', async () => {
    const calls = serve();
    renderApp('/archive/31');

    const linked = await screen.findByRole('heading', { name: 'دورة الصيف 2025' });
    expect(within(linked.closest('article') as HTMLElement).getByRole('button', { expanded: true })).toBeInTheDocument();
    expect(calls).toEqual([31]);
    // The dock / sidebar still marks the archive tab as current.
    expect(screen.getAllByRole('link', { current: 'page' }).some((a) => a.textContent?.includes('الأرشيف'))).toBe(true);
  });

  it('filters by name and by year', async () => {
    serve();
    renderApp('/archive');
    const user = userEvent.setup();

    await user.type(await screen.findByRole('searchbox', { name: 'بحث في الدورات' }), 'الصيف');
    expect(screen.getByRole('heading', { name: 'دورة الصيف 2025' })).toBeInTheDocument();
    expect(screen.queryByRole('heading', { name: 'المستوى الأول 2026' })).not.toBeInTheDocument();

    await user.clear(screen.getByRole('searchbox'));
    await user.selectOptions(screen.getByRole('combobox', { name: 'السنة' }), '2026');
    expect(screen.getAllByRole('article')).toHaveLength(1);

    await user.type(screen.getByRole('searchbox'), 'غير موجود');
    expect(screen.getByRole('heading', { name: 'لا توجد نتائج' })).toBeInTheDocument();
  });

  it('explains an empty archive', async () => {
    serve([]);
    renderApp('/archive');

    expect(await screen.findByRole('heading', { name: 'لا توجد دورات سابقة' })).toBeInTheDocument();
    expect(screen.queryByRole('searchbox')).not.toBeInTheDocument();
  });
});
