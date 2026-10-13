interface ArchPhotoProps {
  /** Tailwind size classes, e.g. `w-11 h-14` (keep the 100:128 arch ratio). */
  className?: string;
  src?: string | null;
  alt?: string;
  /** Shown when there is no photo. */
  initial?: string;
  textClassName?: string;
}

/** Photo framed by the mihrab arch with the gold → teal ring (PLAN.md §2 "Arch mask"). */
export function ArchPhoto({ className = 'w-11 h-14', src, alt = '', initial = '', textClassName = 'text-lg' }: ArchPhotoProps) {
  return (
    <div className={`arch arch-ring shrink-0 ${className}`}>
      <div className="arch w-full h-full grid place-items-center bg-primary text-primary-content overflow-hidden">
        {src ? (
          <img src={src} alt={alt} className="w-full h-full object-cover" />
        ) : (
          <span className={`font-display ${textClassName}`} aria-hidden="true">
            {initial}
          </span>
        )}
      </div>
    </div>
  );
}
