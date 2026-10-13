import { FileText, Lock } from 'lucide-react';
import { API_BASE_URL } from '@/shared/config';
import { t } from '@/shared/i18n';

interface CertificateLinkProps {
  courseId: number;
  /** = final grade set (Q3) */
  published: boolean;
}

/**
 * Opens the admin certificate print view in a new tab, where the student prints it
 * or saves it as PDF. The session cookie travels with the same-origin request.
 */
export function CertificateLink({ courseId, published }: CertificateLinkProps) {
  if (!published) {
    return (
      <button type="button" className="btn btn-lg sm:btn-md w-full sm:w-auto" disabled>
        <Lock className="size-5" strokeWidth={1.75} aria-hidden="true" />
        {t('certificate.locked')}
      </button>
    );
  }

  return (
    <div className="space-y-2">
      <a
        className="btn btn-primary btn-lg sm:btn-md w-full sm:w-auto"
        href={`${API_BASE_URL}/student/courses/${courseId}/certificate`}
        target="_blank"
        rel="noopener"
      >
        <FileText className="size-5" strokeWidth={1.75} aria-hidden="true" />
        {t('certificate.open')}
      </a>
      <p className="text-xs opacity-60">{t('certificate.hint')}</p>
    </div>
  );
}
