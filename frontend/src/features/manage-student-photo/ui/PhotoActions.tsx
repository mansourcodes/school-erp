import { Camera, Image, Pencil, Trash2 } from 'lucide-react';
import { type ReactNode, useRef, useState } from 'react';
import { toApiError } from '@/shared/api';
import { t } from '@/shared/i18n';
import { Dialog, PhotoCropper } from '@/shared/ui';
import { useDeleteStudentPhotoMutation, useUploadStudentPhotoMutation } from '../api/student-photo-api';

interface PhotoActionsProps {
  classroomId: number;
  studentId: number;
  hasPhoto: boolean;
  /** The student's photo; a pen on its corner reveals the photo options. */
  photo: ReactNode;
  onDone: (message: string) => void;
}

/**
 * التقاط صورة (rear camera: the teacher photographs the student) · من المعرض · حذف الصورة with a
 * second in-page confirmation sheet, never `confirm()` (PLAN.md §8 screen 5). The options stay
 * hidden until the pen on the photo is tapped. A picked photo of any size opens the cropper first;
 * only the framed part is uploaded.
 */
export function PhotoActions({ classroomId, studentId, hasPhoto, photo, onDone }: PhotoActionsProps) {
  const camera = useRef<HTMLInputElement>(null);
  const gallery = useRef<HTMLInputElement>(null);
  const [upload, { isLoading: uploading }] = useUploadStudentPhotoMutation();
  const [remove, { isLoading: removing }] = useDeleteStudentPhotoMutation();
  const [confirming, setConfirming] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [picked, setPicked] = useState<File | null>(null);
  const [editing, setEditing] = useState(false);
  const busy = uploading || removing;

  const onFile = (file: File | undefined) => {
    if (!file) return;
    setError(null);
    setPicked(file);
  };

  const onCropped = async (photo: Blob) => {
    try {
      await upload({ classroomId, studentId, photo }).unwrap();
      setEditing(false);
      onDone(t('teacher.photoSaved'));
    } catch (e) {
      setError(toApiError(e).errors?.photo?.[0] ?? t('teacher.photoFailed'));
    }
    setPicked(null);
  };

  return (
    <div className="flex flex-col items-center gap-3 w-full">
      <div className="relative">
        {photo}
        <button
          type="button"
          className={`btn btn-circle btn-sm absolute bottom-1 end-1 shadow-md ${editing ? 'btn-neutral' : 'btn-primary'}`}
          aria-label={t('teacher.editPhoto')}
          aria-expanded={editing}
          aria-controls={`photo-options-${studentId}`}
          onClick={() => setEditing((v) => !v)}
        >
          {uploading ? (
            <span className="loading loading-spinner loading-xs" aria-hidden="true" />
          ) : (
            <Pencil className="size-4" strokeWidth={2} aria-hidden="true" />
          )}
        </button>
      </div>

      {editing && (
        <div id={`photo-options-${studentId}`} className="w-full space-y-2">
          <div className="grid grid-cols-2 gap-2">
            <button type="button" className="btn btn-primary btn-lg" disabled={busy} onClick={() => camera.current?.click()}>
              <Camera className="size-5" strokeWidth={1.75} aria-hidden="true" />
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
        </div>
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
                  setEditing(false);
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
