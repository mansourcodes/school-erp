import { screen } from '@testing-library/react';
import { http, HttpResponse } from 'msw';
import type { AttendanceStats } from '@/entities/attendance';
import type { CourseDetail } from '@/entities/course';
import { renderApp } from '../../../../../test/render-app';
import { demoViewer, server } from '../../../../../test/server';

const stats: AttendanceStats = {
  counts: { present: 40, late: 2, late_excused: 1, absent: 1, absent_excused: 2, unrecorded: 1 },
  recorded: 46,
  attended: 43,
  rate: 93.5,
  punctual: 87,
};

function serve(data: AttendanceStats | null) {
  server.use(
    http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })),
    http.get('*/api/v1/student/courses/current', () =>
      HttpResponse.json({ data: { id: 41, long_name: 'الدورة الحالية', start_date: '2026-09-06', end_date: '2026-12-24' } as CourseDetail }),
    ),
    http.get('*/api/v1/student/attendance/stats', () => HttpResponse.json({ data })),
  );
}

describe('StatsPage', () => {
  it('shows the server numbers: hero, tiles and composition', async () => {
    serve(stats);
    renderApp('/attendance/stats');

    expect(await screen.findByText('93.5%')).toBeInTheDocument();
    expect(screen.getByRole('meter', { name: 'نسبة الحضور' })).toHaveAttribute('aria-valuenow', '94');
    expect(screen.getByText('الحضور في الموعد: 87% · 46 حصة مرصودة · 1 لم تُرصد')).toBeInTheDocument();
    // Lates 2 + 1 excused; absences 1 + 2 excused.
    expect(screen.getAllByText('منها 1 بعذر')).toHaveLength(1);
    expect(screen.getAllByText('منها 2 بعذر')).toHaveLength(1);
    expect(screen.getByRole('img', { name: 'حاضر 40، متأخر 2، متأخر بعذر 1، غائب بعذر 2، غائب 1' })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: 'سجل الحضور التفصيلي' })).toHaveAttribute('href', '/attendance?course=41');
  });

  it('waits for recorded sessions before showing charts', async () => {
    serve({ ...stats, counts: { ...stats.counts, present: 0, late: 0, late_excused: 0, absent: 0, absent_excused: 0 }, recorded: 0, attended: 0, rate: null, punctual: null });
    renderApp('/attendance/stats');

    expect(await screen.findByRole('heading', { name: 'لا توجد بيانات كافية' })).toBeInTheDocument();
  });
});
