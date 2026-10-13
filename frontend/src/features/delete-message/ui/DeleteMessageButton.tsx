import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import { t } from '@/shared/i18n';
import { Dialog } from '@/shared/ui';
import { useDeleteMessageMutation } from '../api/delete-message-api';

/** Delete with a confirmation sheet (never `confirm()`), PLAN.md §8 screen 7. */
export function DeleteMessageButton({ id, recipients, onDeleted }: { id: number; recipients: number; onDeleted: () => void }) {
  const [open, setOpen] = useState(false);
  const [remove, { isLoading }] = useDeleteMessageMutation();
  const titleId = `delete-message-${id}`;

  return (
    <>
      <button type="button" className="btn btn-ghost btn-sm text-error" onClick={() => setOpen(true)}>
        <Trash2 className="size-4" strokeWidth={1.75} aria-hidden="true" />
        {t('messages.delete')}
      </button>
      <Dialog open={open} onClose={() => setOpen(false)} labelledBy={titleId} closeLabel={t('teacher.cancel')}>
        <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
          <div className="sheet-grip" />
          <h2 id={titleId} className="font-display text-lg">
            {t('messages.deleteTitle')}
          </h2>
          <p className="text-sm opacity-75 mt-1 num">{t('messages.deleteBody', { n: recipients })}</p>
          <div className="grid grid-cols-2 gap-2 mt-6">
            <button type="button" className="btn btn-lg" onClick={() => setOpen(false)}>
              {t('teacher.cancel')}
            </button>
            <button
              type="button"
              className="btn btn-error btn-lg"
              disabled={isLoading}
              onClick={async () => {
                await remove(id).unwrap();
                setOpen(false);
                onDeleted();
              }}
            >
              {t('messages.delete')}
            </button>
          </div>
        </div>
      </Dialog>
    </>
  );
}
