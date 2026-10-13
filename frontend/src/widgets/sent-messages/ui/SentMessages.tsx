import { Check, Info, MailX, PenSquare } from 'lucide-react';
import { useState } from 'react';
import { Link } from 'react-router';
import { DeleteMessageButton } from '@/features/delete-message';
import { sentLabel, type SentMessage, useSentMessagesQuery } from '@/entities/message';
import { StudentPhoto } from '@/entities/student';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { EmptyState, ErrorCard, LoadingBlocks } from '@/shared/ui';

/** Teacher sent list (PLAN.md §8 screen 7): newest first, read progress, delete. */
export function SentMessages() {
  const { data, isLoading, isError, refetch } = useSentMessagesQuery(1, { refetchOnFocus: true });
  const [toast, setToast] = useState<string | null>(null);

  if (isLoading) return <LoadingBlocks heights={['h-12', 'h-32', 'h-32']} />;
  if (isError || !data) return <ErrorCard onRetry={refetch} />;

  return (
    <div className="space-y-3">
      <Link to={`${routes.teacher.messages}/new`} className="btn btn-primary btn-lg btn-block">
        <PenSquare className="size-5" strokeWidth={1.75} aria-hidden="true" />
        {t('messages.new')}
      </Link>
      <p className="text-xs opacity-70 flex items-center gap-1.5">
        <Info className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
        {t('messages.teacherNote')}
      </p>

      {data.data.length === 0 ? (
        <div className="card-soft">
          <EmptyState icon={MailX} title={t('messages.sentEmpty')} />
        </div>
      ) : (
        data.data.map((message) => (
          <SentCard
            key={message.id}
            message={message}
            onDeleted={() => {
              setToast(t('messages.deleted'));
              window.setTimeout(() => setToast(null), 3000);
            }}
          />
        ))
      )}

      {toast && (
        <div className="toast toast-top toast-center z-[70]">
          <div role="status" className="alert alert-success shadow-lg">
            <span>{toast}</span>
          </div>
        </div>
      )}
    </div>
  );
}

function SentCard({ message, onDeleted }: { message: SentMessage; onDeleted: () => void }) {
  const percent = message.recipients_count ? Math.round((message.read_count / message.recipients_count) * 100) : 0;

  return (
    <article className="card-soft p-4">
      <header className="flex items-start justify-between gap-2">
        <div className="min-w-0">
          <p className="font-semibold">{t('teacher.room', { room: message.classroom?.number ?? '' })}</p>
          <p className="text-xs opacity-60 num">
            {t('messages.toN', { n: message.recipients_count })} · {sentLabel(message.sent_at)}
          </p>
        </div>
        <DeleteMessageButton id={message.id} recipients={message.recipients_count} onDeleted={onDeleted} />
      </header>
      <p className="mt-2 whitespace-pre-line leading-relaxed">{message.body}</p>

      <details className="mt-3">
        <summary className="cursor-pointer list-none">
          <div className="flex items-center gap-3">
            <div className="flex -space-x-2 rtl:space-x-reverse" aria-hidden="true">
              {message.recipients.slice(0, 4).map((r) => (
                <StudentPhoto key={r.id} name={r.name ?? ''} photoUrl={r.photo_url} className="w-6 h-8" textClassName="text-[10px]" />
              ))}
            </div>
            <span className="text-sm num">{t('messages.readOf', { read: message.read_count, n: message.recipients_count })}</span>
          </div>
          <div className="bar-track mt-2" role="meter" aria-label={t('messages.progress')} aria-valuenow={percent} aria-valuemin={0} aria-valuemax={100}>
            <div className="bar-fill" style={{ width: `${percent}%` }} />
          </div>
        </summary>
        <ul className="mt-3 divide-y divide-base-300">
          {message.recipients.map((r) => (
            <li key={r.id} className="py-2 flex items-center gap-2 text-sm">
              <span className="flex-1 truncate">{r.name}</span>
              {r.read_at ? (
                <span className="badge badge-success badge-soft badge-sm gap-1">
                  <Check className="size-3" strokeWidth={2} aria-hidden="true" />
                  {t('messages.read')}
                </span>
              ) : (
                <span className="badge badge-ghost badge-sm">{t('messages.notRead')}</span>
              )}
            </li>
          ))}
        </ul>
      </details>
    </article>
  );
}
