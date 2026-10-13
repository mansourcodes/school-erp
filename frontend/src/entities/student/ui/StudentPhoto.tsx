import { t } from '@/shared/i18n';

interface StudentPhotoProps {
  name: string;
  photoUrl: string | null;
  /** Tailwind size classes, keep the 100:128 arch ratio (e.g. `w-10 h-13`). */
  className?: string;
  textClassName?: string;
}

/** A student's photo in the arch frame; the initial on a neutral arch when none is set yet. */
export function StudentPhoto({ name, photoUrl, className = 'w-10 h-13', textClassName = 'text-sm' }: StudentPhotoProps) {
  if (photoUrl) {
    return (
      <div className={`arch shrink-0 overflow-hidden bg-base-200 ${className}`}>
        <img src={photoUrl} alt={name} className="w-full h-full object-cover" loading="lazy" />
      </div>
    );
  }
  return (
    <div className={`arch shrink-0 grid place-items-center bg-base-200 text-base-content/50 ${className}`} title={t('teacher.noPhoto')}>
      <span className={`font-display ${textClassName}`} aria-hidden="true">
        {Array.from(name.trim())[0] ?? ''}
      </span>
    </div>
  );
}
