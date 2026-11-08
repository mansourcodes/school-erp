import { AtSign, Building2, Contact, Copy, Fingerprint, IdCard, type LucideIcon, Phone, Smartphone, UserRound } from 'lucide-react';
import type { ReactNode } from 'react';
import type { ClassroomStudentDetail } from '@/entities/classroom';
import { t } from '@/shared/i18n';

/** Bahrain numbers are stored without the country code (8 digits). */
const intl = (phone: string) => (phone.length <= 8 ? `973${phone}` : phone.replace(/^\+|^00/, ''));

/** Contact + personal cards under the calendar in the teacher's student sheet. */
export function StudentContact({ student, onCopied }: { student: ClassroomStudentDetail; onCopied: (message: string) => void }) {
  const copy = (value: string) => {
    void navigator.clipboard?.writeText(value).then(() => onCopied(t('teacher.copied')), () => undefined);
  };

  const contact = [
    student.email && (
      <Row key="email" icon={AtSign} label={t('teacher.email')} value={student.email} onCopy={copy}>
        <a
          className="btn btn-ghost btn-circle btn-sm"
          href={`https://teams.microsoft.com/l/chat/0/0?users=${encodeURIComponent(student.email)}`}
          target="_blank"
          rel="noreferrer"
          aria-label={t('teacher.teams')}
        >
          <TeamsIcon />
        </a>
      </Row>
    ),
    student.mobile2 && (
      <Row key="mobile2" icon={Smartphone} label={t('teacher.guardianPhone')} value={student.mobile2} onCopy={copy}>
        <PhoneActions phone={student.mobile2} label={t('teacher.guardianPhone')} />
      </Row>
    ),
    student.mobile && (
      <Row key="mobile" icon={Phone} label={t('teacher.phone')} value={student.mobile} onCopy={copy}>
        <PhoneActions phone={student.mobile} label={t('teacher.phone')} />
      </Row>
    ),
  ].filter(Boolean);

  const personal = [
    student.cpr && <Row key="cpr" icon={Fingerprint} label={t('teacher.cpr')} value={student.cpr} onCopy={copy} />,
    student.address && <Row key="address" icon={Building2} label={t('teacher.block')} value={student.address} onCopy={copy} />,
    student.iqraa_id_account && (
      <Row key="iqraa" icon={UserRound} label={t('teacher.iqraa')} value={student.iqraa_id_account} onCopy={copy} />
    ),
  ].filter(Boolean);

  return (
    <>
      {contact.length > 0 && <Card icon={Contact} title={t('teacher.contactInfo')}>{contact}</Card>}
      {personal.length > 0 && <Card icon={IdCard} title={t('teacher.personalInfo')}>{personal}</Card>}
    </>
  );
}

function Card({ icon: Icon, title, children }: { icon: LucideIcon; title: string; children: ReactNode }) {
  return (
    <section className="rounded-box border border-base-300 overflow-hidden text-start">
      <h3 className="font-display text-base px-4 py-3 flex items-center gap-2 bg-base-200/50 border-b border-base-300">
        <Icon className="size-5 text-primary" strokeWidth={1.75} aria-hidden="true" />
        {title}
      </h3>
      <dl className="divide-y divide-base-300">{children}</dl>
    </section>
  );
}

function Row({
  icon: Icon,
  label,
  value,
  onCopy,
  children,
}: {
  icon: LucideIcon;
  label: string;
  value: string;
  onCopy: (value: string) => void;
  children?: ReactNode;
}) {
  return (
    <div className="flex items-center gap-3 px-4 py-3">
      <span className="size-10 rounded-field bg-base-200 grid place-items-center shrink-0">
        <Icon className="size-5 opacity-70" strokeWidth={1.75} aria-hidden="true" />
      </span>
      <div className="min-w-0 flex-1">
        <dt className="text-xs opacity-60">{label}</dt>
        <dd className="flex items-center gap-1.5 font-semibold">
          <span className="num ltr truncate">{value}</span>
          <button
            type="button"
            className="btn btn-ghost btn-xs btn-circle text-info shrink-0"
            aria-label={t('teacher.copy', { label })}
            onClick={() => onCopy(value)}
          >
            <Copy className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
          </button>
        </dd>
      </div>
      {children && <div className="flex items-center gap-1 shrink-0">{children}</div>}
    </div>
  );
}

function PhoneActions({ phone, label }: { phone: string; label: string }) {
  return (
    <>
      <a className="btn btn-ghost btn-circle btn-sm text-success" href={`tel:${phone}`} aria-label={t('teacher.call', { label })}>
        <Phone className="size-5" strokeWidth={1.75} aria-hidden="true" />
      </a>
      <a
        className="btn btn-ghost btn-circle btn-sm"
        href={`https://wa.me/${intl(phone)}`}
        target="_blank"
        rel="noreferrer"
        aria-label={t('teacher.whatsapp', { label })}
      >
        <WhatsAppIcon />
      </a>
    </>
  );
}

// Brand marks (lucide ships no brand icons).
function WhatsAppIcon() {
  return (
    <svg viewBox="0 0 24 24" className="size-6" fill="#25D366" aria-hidden="true">
      <path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.21 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35M12.05 21.5h-.01a9.4 9.4 0 0 1-4.8-1.32l-.34-.2-3.57.94.95-3.48-.22-.36a9.4 9.4 0 0 1-1.44-5.02c0-5.2 4.23-9.43 9.44-9.43 2.52 0 4.89.98 6.67 2.77a9.37 9.37 0 0 1 2.76 6.67c0 5.2-4.24 9.43-9.44 9.43m8.03-17.46A11.28 11.28 0 0 0 12.05.72C5.8.72.7 5.8.7 12.06c0 2 .52 3.95 1.52 5.67L.6 23.6l6.01-1.58a11.33 11.33 0 0 0 5.43 1.38h.01c6.25 0 11.34-5.08 11.35-11.34 0-3.03-1.18-5.88-3.32-8.02" />
    </svg>
  );
}

function TeamsIcon() {
  return (
    <svg viewBox="0 0 24 24" className="size-6" aria-hidden="true">
      <circle cx="19" cy="6.5" r="2.25" fill="#7B83EB" />
      <rect x="15.5" y="9.5" width="7" height="8.5" rx="2.5" fill="#7B83EB" />
      <circle cx="12.5" cy="5" r="3" fill="#5059C9" />
      <rect x="7" y="9" width="11" height="11" rx="3" fill="#5059C9" />
      <rect x="1.5" y="6.5" width="11" height="11" rx="1.5" fill="#4B53BC" />
      <path d="M4.5 9.5h5v1.4H7.8v4.6H6.2v-4.6H4.5z" fill="#fff" />
    </svg>
  );
}
