import { screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import type { CourseDetail } from '@/entities/course';
import { renderApp } from '../../../../../test/render-app';
import { demoViewer, server } from '../../../../../test/server';

// Shaped like CourseDetailResource (PLAN.md §7.3).
const course: CourseDetail = {
  id: 41,
  classroom_id: 912,
  status: 'active',
  long_name: 'المسار الحوزوي [2026] 1448 [الفصل الأول]',
  academic_path: 'المسار الحوزوي',
  year: '2026',
  hijri_year: '1448',
  semester: 'الفصل الأول',
  start_date: '2026-09-04',
  end_date: '2026-12-26',
  duration: '96',
  class_room_number: '2-ب',
  class_room_name: 'قاعة الإمام الصادق',
  curricula: [
    { id: 11, name: 'تجويد القرآن الكريم', teacher: 'الشيخ أحمد', slots: [{ day: 5, start: '16:00', end: '17:00' }] },
    { id: 12, name: 'الفقه', teacher: 'الشيخ محمد', slots: [{ day: 6, start: '17:15', end: '18:15' }] },
  ],
  payments: [
    { id: 1043, date: '2026-09-07', amount: 30, type: 'رسوم كاملة', source: 'نقدًا' },
    { id: 1102, date: '2026-10-05', amount: 15, type: 'دفعة جزئية', source: 'بنفت باي' },
  ],
  marks_published: false,
  marks: [
    { key: 'm0', label: 'الاختبار الشفوي', max: 60, mark: 51 },
    { key: 'm1', label: 'الحفظ', max: 40, mark: null },
  ],
  total_mark: null,
  final_grade: null,
};

function serve(data: CourseDetail | null) {
  server.use(
    http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })),
    http.get('*/api/v1/student/courses/current', () => HttpResponse.json({ data })),
  );
}

describe('HomePage', () => {
  it('shows the current course with its timetable', async () => {
    serve(course);
    renderApp('/');

    expect(await screen.findByRole('heading', { name: course.long_name })).toBeInTheDocument();
    expect(screen.getByText('الفصل الأول · 1448هـ · الصف 2-ب')).toBeInTheDocument();
    expect(screen.getAllByText('تجويد القرآن الكريم').length).toBeGreaterThan(0);
    expect(screen.getByText('45.000 د.ب')).toBeInTheDocument();
  });

  it('opens the payments tab from the KPI tile and shows a receipt', async () => {
    serve(course);
    const { router } = renderApp('/');
    const user = userEvent.setup();

    await user.click(await screen.findByRole('button', { name: /المدفوع/ }));

    expect(router.state.location.hash).toBe('#payments');
    expect(screen.getByRole('tab', { name: 'المدفوعات' })).toHaveAttribute('aria-selected', 'true');

    await user.click(screen.getByRole('button', { name: 'عرض الإيصال رقم 1102' }));
    const receipt = screen.getByRole('dialog', { name: 'إيصال استلام نقدي' });
    expect(within(receipt).getByText(demoViewer.name)).toBeInTheDocument();
    expect(within(receipt).getByText('15.000 د.ب')).toBeInTheDocument();
    expect(within(receipt).getByText(`دفعة جزئية — ${course.long_name}`)).toBeInTheDocument();
  });

  it('deep-links to the marks tab and keeps the certificate locked while marks are in progress', async () => {
    serve(course);
    renderApp('/#marks');

    expect(await screen.findByText(/الدرجات قيد الرصد — المعتمد حتى الآن 51 من 100/)).toBeInTheDocument();
    expect(screen.getByText('لم تُرصد بعد')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'الشهادة غير متاحة بعد' })).toBeDisabled();
  });

  it('links to the certificate once the final grade is published', async () => {
    serve({
      ...course,
      marks_published: true,
      marks: [
        { key: 'm0', label: 'الاختبار الشفوي', max: 60, mark: 51 },
        { key: 'm1', label: 'الحفظ', max: 40, mark: 38 },
      ],
      total_mark: 89,
      final_grade: 'ممتاز',
    });
    renderApp('/#marks');

    const link = await screen.findByRole('link', { name: 'عرض الشهادة للطباعة' });
    expect(link).toHaveAttribute('href', expect.stringMatching(/\/api\/v1\/student\/courses\/41\/certificate$/));
    expect(link).toHaveAttribute('target', '_blank');
    expect(screen.getAllByText('ممتاز').length).toBeGreaterThan(0);
  });

  it('explains when there is no active course', async () => {
    serve(null);
    renderApp('/');

    expect(await screen.findByRole('heading', { name: 'لا توجد دورة نشطة حاليًا' })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: 'تصفّح الدورات السابقة' })).toHaveAttribute('href', '/archive');
  });

  it('offers a retry when loading fails', async () => {
    let fail = true;
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })),
      http.get('*/api/v1/student/courses/current', () =>
        fail ? HttpResponse.json({ message: 'x' }, { status: 500 }) : HttpResponse.json({ data: course }),
      ),
    );
    renderApp('/');
    const user = userEvent.setup();

    await user.click(await screen.findByRole('button', { name: 'إعادة المحاولة' }));
    fail = false;
    await user.click(screen.getByRole('button', { name: 'إعادة المحاولة' }));

    expect(await screen.findByRole('heading', { name: course.long_name })).toBeInTheDocument();
  });
});
