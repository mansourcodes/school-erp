import { useCallback } from 'react';
import { useNavigate, useSearchParams } from 'react-router';
import { ComposeForm } from '@/features/send-message';
import { routes } from '@/shared/config';

/** /teacher/messages/new?room= (PLAN.md §8 screen 8), a task screen. */
export function TeacherComposePage() {
  const [params] = useSearchParams();
  const navigate = useNavigate();
  const room = Number(params.get('room')) || undefined;
  const onSent = useCallback(() => navigate(routes.teacher.messages, { replace: true, state: { sent: true } }), [navigate]);

  return <ComposeForm initialClassroomId={room} onSent={onSent} />;
}
