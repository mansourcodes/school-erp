import { Info, MailX } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useLocation } from 'react-router';
import { useMarkMessageReadMutation } from '@/features/read-message';
import { type InboxMessage, MessageCard, useInboxQuery } from '@/entities/message';
import { t } from '@/shared/i18n';
import { EmptyState, ErrorCard, LoadingBlocks } from '@/shared/ui';

/** Student inbox (PLAN.md §3.7): read-only, newest first, older pages on demand. */
export function InboxList() {
  const [page, setPage] = useState(1);
  const [older, setOlder] = useState<InboxMessage[]>([]);
  const first = useInboxQuery(1, { refetchOnFocus: true });
  const next = useInboxQuery(page, { skip: page === 1 });
  const [markRead] = useMarkMessageReadMutation();
  const { hash } = useLocation();
  const target = hash.startsWith('#') ? Number(hash.slice(1)) : null;

  // Accumulate older pages below the live first page.
  useEffect(() => {
    if (page > 1 && next.data) setOlder((list) => [...list, ...next.data!.data.filter((m) => !list.some((x) => x.id === m.id))]);
  }, [page, next.data]);

  const messages = [...(first.data?.data ?? []), ...older.filter((m) => !first.data?.data.some((x) => x.id === m.id))];
  const lastPage = (page === 1 ? first.data?.meta.last_page : next.data?.meta.last_page) ?? 1;
  const missing = target !== null && first.data && !messages.some((m) => m.id === target);

  useEffect(() => {
    if (target && messages.some((m) => m.id === target)) document.getElementById(`message-${target}`)?.scrollIntoView({ block: 'center' });
  }, [target, messages.length]); // eslint-disable-line react-hooks/exhaustive-deps

  if (first.isLoading) return <LoadingBlocks heights={['h-28', 'h-28', 'h-28']} />;
  if (first.isError || !first.data) return <ErrorCard onRetry={first.refetch} />;

  return (
    <div className="space-y-3">
      <div role="note" className="alert alert-soft text-sm">
        <Info className="size-5" strokeWidth={1.75} aria-hidden="true" />
        <span>{t('messages.noReply')}</span>
      </div>
      {missing && (
        // Opened from an old notification of a message the teacher deleted (PLAN.md §3.7).
        <div role="alert" className="alert alert-soft alert-warning text-sm">
          <MailX className="size-5" strokeWidth={1.75} aria-hidden="true" />
          <span>{t('messages.gone')}</span>
        </div>
      )}
      {messages.length === 0 ? (
        <div className="card-soft">
          <EmptyState icon={MailX} title={t('messages.empty')} body={t('messages.emptyBody')} />
        </div>
      ) : (
        messages.map((message) => (
          <MessageCard key={message.id} message={message} onOpen={(m) => void markRead(m.id)} />
        ))
      )}
      {page < lastPage && (
        <button type="button" className="btn btn-ghost btn-block" disabled={next.isFetching} onClick={() => setPage(page + 1)}>
          {t('messages.showMore')}
        </button>
      )}
    </div>
  );
}
