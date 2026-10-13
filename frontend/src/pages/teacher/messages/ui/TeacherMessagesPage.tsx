import { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router';
import { SentMessages } from '@/widgets/sent-messages';
import { t } from '@/shared/i18n';

/** /teacher/messages (PLAN.md §8 screen 7); shows "تم إرسال الرسالة" when coming back from compose. */
export function TeacherMessagesPage() {
  const location = useLocation();
  const navigate = useNavigate();
  const [sent, setSent] = useState(Boolean((location.state as { sent?: boolean } | null)?.sent));

  useEffect(() => {
    if (!sent) return;
    navigate(location.pathname, { replace: true, state: null });
    const timer = window.setTimeout(() => setSent(false), 3000);
    return () => window.clearTimeout(timer);
  }, [sent]); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <>
      <SentMessages />
      {sent && (
        <div className="toast toast-top toast-center z-[70]">
          <div role="status" className="alert alert-success shadow-lg">
            <span>{t('messages.sent')}</span>
          </div>
        </div>
      )}
    </>
  );
}
