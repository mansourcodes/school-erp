import { useCallback } from 'react';
import { useNavigate, useSearchParams } from 'react-router';
import { ComposeForm, type SendMessageResult } from '@/features/send-message';
import { routes } from '@/shared/config';

/** /teacher/messages/new?room= (PLAN.md §8 screen 8), a task screen. */
export function TeacherComposePage() {
  const [params] = useSearchParams();
  const navigate = useNavigate();
  const room = Number(params.get('room')) || undefined;
  const onSent = useCallback(
    (result: SendMessageResult) =>
      result.push_pending > 0
        ? // The students' notifications are sent one by one from the next page (push outbox).
          navigate(routes.teacher.notify, { replace: true, state: { saved: 'message', next: routes.teacher.messages } })
        : navigate(routes.teacher.messages, { replace: true, state: { sent: true } }),
    [navigate],
  );

  return <ComposeForm initialClassroomId={room} onSent={onSent} />;
}
