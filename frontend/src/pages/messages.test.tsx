import { screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import type { ClassroomDetail, TeacherClassroom } from '@/entities/classroom';
import type { InboxMessage, SentMessage } from '@/entities/message';
import { renderApp } from '../../test/render-app';
import { demoViewer, server } from '../../test/server';

const page = <T,>(data: T[]) => ({ data, meta: { current_page: 1, last_page: 1 } });
const inboxMessage = (over: Partial<InboxMessage>): InboxMessage => ({
  id: 9003,
  teacher: 'السيد حسن الموسوي',
  teacher_photo_url: null,
  classroom: { id: 912, number: '2-ب', name: null },
  body: 'يرجى مراجعة الدرس الثالث قبل الاختبار القصير.',
  sent_at: '2026-10-12T13:20:00+03:00',
  read_at: null,
  ...over,
});

describe('student inbox', () => {
  it('shows the unread badge and marks a message read when opened', async () => {
    let unread = 1;
    let readCalls = 0;
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: { ...demoViewer, unread_messages: unread } })),
      http.get('*/api/v1/student/messages', () => HttpResponse.json(page([inboxMessage({ read_at: unread ? null : '2026-10-12T14:00:00+03:00' })]))),
      http.post('*/api/v1/student/messages/:id/read', () => {
        readCalls++;
        unread = 0;
        return HttpResponse.json({ data: { read_at: '2026-10-12T14:00:00+03:00' } });
      }),
    );
    renderApp('/messages');
    const user = userEvent.setup();

    expect(await screen.findByRole('link', { name: 'الرسائل، 1 غير مقروءة' })).toBeInTheDocument();
    expect(await screen.findByText('لا يمكن الرد على الرسائل. للاستفسار راجع مدرّسك في الصف أو إدارة المركز.')).toBeInTheDocument();
    // No reply box, no compose.
    expect(screen.queryByRole('textbox')).not.toBeInTheDocument();

    await user.click(screen.getByText(/يرجى مراجعة الدرس الثالث/));
    await waitFor(() => expect(readCalls).toBe(1));
    // Badge gone: the envelope (and the sidebar entry) are plain "الرسائل" again.
    await waitFor(() => expect(screen.queryByRole('link', { name: 'الرسائل، 1 غير مقروءة' })).not.toBeInTheDocument());
    expect(screen.getAllByRole('link', { name: 'الرسائل' }).length).toBeGreaterThan(0);
  });

  it('says a message from an old notification is no longer available', async () => {
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })),
      http.get('*/api/v1/student/messages', () => HttpResponse.json(page([inboxMessage({ id: 1 })]))),
    );
    renderApp('/messages#4242');

    expect(await screen.findByText('لم تعد هذه الرسالة متاحة')).toBeInTheDocument();
  });
  it("shows the teacher's photo next to their name", async () => {
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })),
      http.get('*/api/v1/student/messages', () =>
        HttpResponse.json(page([inboxMessage({ teacher_photo_url: '/storage/teachers/photos/5/a.webp', read_at: '2026-10-12T14:00:00+03:00' })])),
      ),
    );
    const { container } = renderApp('/messages');

    expect(await screen.findByText('السيد حسن الموسوي')).toBeInTheDocument();
    expect(container.querySelector('article img')).toHaveAttribute('src', '/storage/teachers/photos/5/a.webp');
  });
});

describe('teacher messages', () => {
  const teacher = { role: 'teacher' as const, id: 5, name: 'الشيخ أحمد', first_name: 'الشيخ', email: 't@example.com', photo_url: null };
  const classrooms = [{ id: 912, number: '2-ب', name: null } as TeacherClassroom];
  const detail = {
    id: 912,
    number: '2-ب',
    students: [
      { id: 1, name: 'حسين علي', student_id: '20240001', photo_url: null },
      { id: 2, name: 'محمد جعفر', student_id: '20240002', photo_url: null },
    ],
  } as ClassroomDetail;

  it('composes to all students of a classroom and returns to the sent list', async () => {
    let sent: unknown;
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: teacher })),
      http.get('*/api/v1/teacher/classrooms', () => HttpResponse.json({ data: classrooms })),
      http.get('*/api/v1/teacher/classrooms/:id', () => HttpResponse.json({ data: detail })),
      http.get('*/api/v1/teacher/messages', () => HttpResponse.json(page([]))),
      http.post('*/api/v1/teacher/messages', async ({ request }) => {
        sent = await request.json();
        return HttpResponse.json({ data: { id: 1, recipients_count: 2 } }, { status: 201 });
      }),
    );
    const { router } = renderApp('/teacher/messages/new?room=912');
    const user = userEvent.setup();

    const send = await screen.findByRole('button', { name: 'إرسال إلى 0' });
    expect(send).toBeDisabled();
    await user.click(await screen.findByRole('button', { name: 'تحديد الكل' }));
    await user.type(screen.getByRole('textbox'), 'غدًا اختبار قصير');
    expect(screen.getByText('16 / 500')).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'إرسال للجميع' }));

    await waitFor(() => expect(router.state.location.pathname).toBe('/teacher/messages'));
    expect(sent).toEqual({ classroom_id: 912, student_ids: [1, 2], body: 'غدًا اختبار قصير' });
    expect(await screen.findByText('تم إرسال الرسالة')).toBeInTheDocument();
  });

  it('shows read progress and deletes after confirmation', async () => {
    let deleted = false;
    const message: SentMessage = {
      id: 77,
      classroom: { id: 912, number: '2-ب', name: null },
      body: 'تذكير بموعد الاختبار',
      sent_at: '2026-10-12T13:20:00+03:00',
      recipients_count: 2,
      read_count: 1,
      recipients: [
        { id: 1, name: 'حسين علي', photo_url: null, read_at: '2026-10-12T14:00:00+03:00' },
        { id: 2, name: 'محمد جعفر', photo_url: null, read_at: null },
      ],
    };
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: teacher })),
      http.get('*/api/v1/teacher/messages', () => HttpResponse.json(page(deleted ? [] : [message]))),
      http.delete('*/api/v1/teacher/messages/:id', () => {
        deleted = true;
        return new HttpResponse(null, { status: 204 });
      }),
    );
    renderApp('/teacher/messages');
    const user = userEvent.setup();

    expect(await screen.findByText('قرأها 1 من 2')).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'حذف' }));
    const sheet = screen.getByRole('dialog', { name: 'حذف الرسالة؟' });
    expect(within(sheet).getByText(/ستُحذف من صناديق 2 طالب فورًا/)).toBeInTheDocument();
    await user.click(within(sheet).getByRole('button', { name: 'حذف' }));

    expect(await screen.findByText('لم ترسل أي رسالة بعد')).toBeInTheDocument();
  });
});
