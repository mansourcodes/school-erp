import { ImageUp, Trash2 } from 'lucide-react';
import { useRef, useState } from 'react';
import { toApiError } from '@/shared/api';
import { t } from '@/shared/i18n';
import { resizeImage } from '@/shared/lib';
import { Dialog } from '@/shared/ui';
import { useDeleteTeacherPhotoMutation, useUploadTeacherPhotoMutation } from '../api/teacher-photo-api';

interface TeacherPhotoActionsProps {
  hasPhoto: boolean;
}

/**
 * Change / remove the teacher's own photo (profile). No `capture`, so phones offer both the
 * camera and the gallery. Deleting asks in an in-page sheet, never `confirm()`.
 */
export function TeacherPhotoActions({ hasPhoto }: TeacherPhotoActionsProps) {
  const input = useRef<HTMLInputElement>(null);
  const [upload, { isLoading: uploading }] = useUploadTeacherPhotoMutation();
  const [remove, { isLoading: removing }] = useDeleteTeacherPhotoMutation();
  const [confirming, setConfirming] = useState(false);
  const [status, setStatus] = useState<{ ok: boolean; text: string } | null>(null);
  const busy = uploading || removing;

  const onFile = async (file: File | undefined) => {
    if (!file) return;
    setStatus(null);
    try {
      await upload(await resizeImage(file)).unwrap();
      setStatus({ ok: true, text: t('teacher.photoSaved') });
    } catch (e) {
      setStatus({ ok: false, text: toApiError(e).errors?.photo?.[0] ?? t('teacher.photoFailed') });
    }
  };

  return (
    <div className="space-y-2">
      <p className="text-sm opacity-80">{t('teacher.ownPhotoNote')}</p>
      <div className={`grid gap-2 ${hasPhoto ? 'grid-cols-2' : ''}`}>
        <button type="button" className="btn btn-primary btn-lg sm:btn-md" disabled={busy} onClick={() => input.current?.click()}>
          {uploading ? (
            <span className="loading loading-spinner loading-sm" aria-hidden="true" />
          ) : (
            <ImageUp className="size-5" strokeWidth={1.75} aria-hidden="true" />
          )}
          {hasPhoto ? t('teacher.changeOwnPhoto') : t('teacher.addOwnPhoto')}
        </button>
        {hasPhoto && (
          <button type="button" className="btn btn-ghost btn-lg sm:btn-md text-error" disabled={busy} onClick={() => setConfirming(true)}>
            <Trash2 className="size-4" strokeWidth={1.75} aria-hidden="true" />
            {t('teacher.deletePhoto')}
          </button>
        )}
      </div>
      {status && (
        <p role={status.ok ? 'status' : 'alert'} className={`text-sm ${status.ok ? 'text-success' : 'text-error'}`}>
          {status.text}
        </p>
      )}

      <input
        ref={input}
        type="file"
        accept="image/jpeg,image/png,image/webp"
        hidden
        data-testid="teacher-photo-input"
        onChange={(e) => {
          void onFile(e.target.files?.[0]);
          e.target.value = '';
        }}
      />

      <Dialog open={confirming} onClose={() => setConfirming(false)} labelledBy="delete-own-photo-title" closeLabel={t('teacher.cancel')}>
        <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
          <div className="sheet-grip" />
          <h2 id="delete-own-photo-title" className="font-display text-lg">
            {t('teacher.deleteOwnConfirmTitle')}
          </h2>
          <p className="text-sm opacity-75 mt-1">{t('teacher.deleteOwnConfirmBody')}</p>
          <div className="grid grid-cols-2 gap-2 mt-6">
            <button type="button" className="btn btn-lg" onClick={() => setConfirming(false)}>
              {t('teacher.cancel')}
            </button>
            <button
              type="button"
              className="btn btn-error btn-lg"
              disabled={removing}
              onClick={async () => {
                try {
                  await remove().unwrap();
                  setStatus({ ok: true, text: t('teacher.photoDeleted') });
                } catch {
                  setStatus({ ok: false, text: t('teacher.photoFailed') });
                }
                setConfirming(false);
              }}
            >
              {t('teacher.confirmDelete')}
            </button>
          </div>
        </div>
      </Dialog>
    </div>
  );
}
