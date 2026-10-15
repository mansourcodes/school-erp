import { useState } from 'react';
import { t } from '@/shared/i18n';
import { sentLabel } from '../lib/when';
import type { InboxMessage } from '../model/types';

interface MessageCardProps {
  message: InboxMessage;
  /** First open of an unread message marks it read. */
  onOpen: (message: InboxMessage) => void;
}

/**
 * Inbox card (PLAN.md §3.7): teacher (arch initial), classroom, relative time, body clamped to
 * 3 lines with "عرض المزيد". Unread = tinted + dot. Rendered as text only (never HTML).
 */
export function MessageCard({ message, onOpen }: MessageCardProps) {
  const [expanded, setExpanded] = useState(false);
  const unread = !message.read_at;
  const long = message.body.length > 160;

  return (
    <article
      id={`message-${message.id}`}
      className={`card-soft p-4 ${unread ? 'border-primary/40 bg-primary/5' : ''}`}
      onClick={() => unread && onOpen(message)}
    >
      <header className="flex items-center gap-3">
        {message.teacher_photo_url ? (
          <img src={message.teacher_photo_url} alt="" className="arch w-9 h-11 shrink-0 object-cover bg-base-200" loading="lazy" />
        ) : (
          <div className="arch w-9 h-11 shrink-0 grid place-items-center bg-primary text-primary-content">
            <span className="font-display" aria-hidden="true">
              {Array.from((message.teacher ?? '').trim())[0] ?? ''}
            </span>
          </div>
        )}
        <div className="min-w-0 flex-1">
          <p className={`truncate ${unread ? 'font-semibold' : 'font-medium'}`}>{message.teacher}</p>
          <p className="text-xs opacity-60 num">
            {message.classroom?.number ? t('teacher.room', { room: message.classroom.number }) + ' · ' : ''}
            {sentLabel(message.sent_at)}
            {message.edited_at && ` · ${t('messages.edited')}`}
          </p>
        </div>
        {unread && <span className="size-2.5 rounded-full bg-primary shrink-0" aria-label={t('messages.unread')} />}
      </header>
      <p className={`mt-3 whitespace-pre-line leading-relaxed ${unread ? 'font-medium' : ''} ${expanded ? '' : 'line-clamp-3'}`}>
        {message.body}
      </p>
      {long && (
        <button
          type="button"
          className="link link-primary text-sm mt-1 no-underline"
          aria-expanded={expanded}
          onClick={(event) => {
            event.stopPropagation();
            setExpanded((v) => !v);
            if (unread) onOpen(message);
          }}
        >
          {expanded ? t('messages.showLess') : t('messages.showMore')}
        </button>
      )}
    </article>
  );
}
