import { Camera, Image, Trash2 } from 'lucide-react';
import { useRef, useState } from 'react';
import { toApiError } from '@/shared/api';
import { t } from '@/shared/i18n';
import { Dialog, PhotoCropper } from '@/shared/ui';
import { useDeleteStudentPhotoMutation, useUploadStudentPhotoMutation } from '../api/student-photo-api';

interface PhotoActionsProps {
  classroomId: number;
  studentId: number;
  hasPhoto: boolean;
  onDone: (message: string) => void;
}

/**
 * التقاط صورة (rear camera: the teacher photographs the student) · من المعرض · حذف الصورة with a
 * second in-page confirmation sheet, never `confirm()` (PLAN.md §8 screen 5). A picked photo of any
 * size opens the cropper first; only the framed part is uploaded.
 */
export function PhotoActions({ classroomId, studentId, hasPhoto, onDone }: PhotoActionsProps) {
  const camera = useRef<HTMLInputElement>(null);
  const gallery = useRef<HTMLInputElement>(null);
  const [upload, { isLoading: uploading }] = useUploadStudentPhotoMutation();
  const [remove, { isLoading: removing }] = useDeleteStudentPhotoMutation();
  const [confirming, setConfirming] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [picked, setPicked] = useState<File | null>(null);
  const busy = uploading || removing;

  const onFile = (file: File | undefined) => {
    if (!file) return;
    setError(null);
    setPicked(file);
  };

  const onCropped = async (photo: Blob) => {
    try {
      await upload({ classroomId, studentId, photo }).unwrap();
      onDone(t('teacher.photoSaved'));
    } catch (e) {
      setError(toApiError(e).errors?.photo?.[0] ?? t('teacher.photoFailed'));
    }
    setPicked(null);
  };

  return (
    <div className="space-y-2">
      <div className="grid grid-cols-2 gap-2">
        <button type="button" className="btn btn-primary btn-lg" disabled={busy} onClick={() => camera.current?.click()}>
          {uploading ? (
            <span className="loading loading-spinner loading-sm" aria-hidden="true" />
          ) : (
            <Camera className="size-5" strokeWidth={1.75} aria-hidden="true" />
          )}
          {t('teacher.takePhoto')}
        </button>
        <button type="button" className="btn btn-lg" disabled={busy} onClick={() => gallery.current?.click()}>
          <Image className="size-5" strokeWidth={1.75} aria-hidden="true" />
          {t('teacher.fromGallery')}
        </button>
      </div>
      {hasPhoto && (
        <button type="button" className="btn btn-ghost btn-block text-error" disabled={busy} onClick={() => setConfirming(true)}>
          <Trash2 className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('teacher.deletePhoto')}
        </button>
      )}
      {error && (
        <p role="alert" className="text-sm text-error">
          {error}
        </p>
      )}

      <input
        ref={camera}
        type="file"
        accept="image/*"
        capture="environment"
        hidden
        onChange={(e) => {
          onFile(e.target.files?.[0]);
          e.target.value = '';
        }}
      />
      <input
        ref={gallery}
        type="file"
        accept="image/jpeg,image/png,image/webp"
        hidden
        onChange={(e) => {
          onFile(e.target.files?.[0]);
          e.target.value = '';
        }}
      />

      <PhotoCropper file={picked} busy={uploading} onCancel={() => setPicked(null)} onConfirm={(photo) => void onCropped(photo)} />

      <Dialog open={confirming} onClose={() => setConfirming(false)} labelledBy="delete-photo-title" closeLabel={t('teacher.cancel')}>
        <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
          <div className="sheet-grip" />
          <h2 id="delete-photo-title" className="font-display text-lg">
            {t('teacher.deleteConfirmTitle')}
          </h2>
          <p className="text-sm opacity-75 mt-1">{t('teacher.deleteConfirmBody')}</p>
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
                  await remove({ classroomId, studentId }).unwrap();
                  setConfirming(false);
                  onDone(t('teacher.photoDeleted'));
                } catch {
                  setError(t('teacher.photoFailed'));
                  setConfirming(false);
                }
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
