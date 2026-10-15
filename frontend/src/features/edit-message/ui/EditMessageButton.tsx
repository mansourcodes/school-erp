import { Pencil } from 'lucide-react';
import { useState } from 'react';
import { MESSAGE_MAX } from '@/entities/message';
import { t } from '@/shared/i18n';
import { Dialog } from '@/shared/ui';
import { useEditMessageMutation } from '../api/edit-message-api';

/** Edit the text in a sheet; recipients and read state stay as sent. */
export function EditMessageButton({
  id,
  body,
  recipients,
  onEdited,
}: {
  id: number;
  body: string;
  recipients: number;
  onEdited: () => void;
}) {
  const [open, setOpen] = useState(false);
  const [draft, setDraft] = useState(body);
  const [failed, setFailed] = useState(false);
  const [save, { isLoading }] = useEditMessageMutation();
  const titleId = `edit-message-${id}`;
  const text = draft.trim();

  return (
    <>
      <button
        type="button"
        className="btn btn-ghost btn-sm"
        onClick={() => {
          setDraft(body);
          setFailed(false);
          setOpen(true);
        }}
      >
        <Pencil className="size-4" strokeWidth={1.75} aria-hidden="true" />
        {t('messages.edit')}
      </button>
      <Dialog open={open} onClose={() => setOpen(false)} labelledBy={titleId} closeLabel={t('teacher.cancel')}>
        <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
          <div className="sheet-grip" />
          <h2 id={titleId} className="font-display text-lg">
            {t('messages.editTitle')}
          </h2>
          <p className="text-sm opacity-75 mt-1 num">{t('messages.editBody', { n: recipients })}</p>
          <textarea
            aria-label={t('messages.stepText')}
            className="textarea w-full min-h-36 mt-4"
            maxLength={MESSAGE_MAX}
            value={draft}
            onChange={(e) => setDraft(e.target.value)}
          />
          <p className="text-xs opacity-70 text-end num mt-1" aria-live="polite">
            {t('messages.counter', { n: draft.length, max: MESSAGE_MAX })}
          </p>
          {failed && (
            <p role="alert" className="text-sm text-error mt-2">
              {t('messages.editFailed')}
            </p>
          )}
          <div className="grid grid-cols-2 gap-2 mt-6">
            <button type="button" className="btn btn-lg" onClick={() => setOpen(false)}>
              {t('teacher.cancel')}
            </button>
            <button
              type="button"
              className="btn btn-primary btn-lg"
              disabled={isLoading || !text || text === body.trim()}
              onClick={async () => {
                setFailed(false);
                try {
                  await save({ id, body: text }).unwrap();
                  setOpen(false);
                  onEdited();
                } catch {
                  setFailed(true);
                }
              }}
            >
              {t('messages.save')}
            </button>
          </div>
        </div>
      </Dialog>
    </>
  );
}
