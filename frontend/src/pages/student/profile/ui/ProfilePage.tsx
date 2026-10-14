import {
  ArrowUpRight,
  Eye,
  EyeOff,
  GraduationCap,
  IdCard,
  Info,
  KeyRound,
  type LucideIcon,
  Phone,
  RefreshCw,
  WifiOff,
} from 'lucide-react';
import { type ReactNode, useState } from 'react';
import { LogoutButton } from '@/features/auth/logout';
import { NotificationsCard } from '@/features/enable-notifications';
import { maskCpr, type Profile, useMeQuery, useProfileQuery, ViewerAvatar } from '@/entities/viewer';
import { LEGACY_PORTAL_URL } from '@/shared/config';
import { t } from '@/shared/i18n';
import { fmtDate } from '@/shared/lib';
import { EmptyState } from '@/shared/ui';

/** /profile (PLAN.md §3.6): display only; the photo is set by the teacher or admin (Q4). */
export function ProfilePage() {
  const { data: viewer } = useMeQuery();
  const { data: profile, isLoading, isError, refetch } = useProfileQuery();

  return (
    <div className="space-y-5">
      {isLoading || !viewer ? (
        <div className="space-y-4" aria-busy="true">
          <div className="skeleton h-44 w-full rounded-box" />
          <div className="skeleton h-56 w-full rounded-box" />
        </div>
      ) : isError || !profile ? (
        <div className="card-soft">
          <EmptyState
            icon={WifiOff}
            title={t('app.loadFailed')}
            action={
              <button type="button" className="btn btn-primary mt-2" onClick={() => refetch()}>
                <RefreshCw className="size-4" strokeWidth={1.75} aria-hidden="true" />
                {t('app.retry')}
              </button>
            }
          />
        </div>
      ) : (
        <>
          <section className="card-soft overflow-hidden">
            <div className="hero-band bg-pattern h-24 sm:h-28" />
            <div className="px-5 sm:px-7 pb-6 -mt-14 sm:-mt-16 flex flex-col sm:flex-row sm:items-end gap-4">
              <ViewerAvatar viewer={viewer} className="w-24 h-[7.5rem] sm:w-28 sm:h-36" textClassName="text-4xl" />
              <div className="flex-1 min-w-0">
                <h2 className="font-display text-xl sm:text-2xl leading-snug">{profile.name}</h2>
                <p className="text-sm opacity-70 flex flex-wrap gap-x-4 mt-1">
                  <span className="num">{t('profile.studentNumber', { id: profile.student_id })}</span>
                  {profile.registration_at && <span>{t('profile.since', { date: fmtDate(profile.registration_at) })}</span>}
                </p>
              </div>
              <p className="text-xs opacity-60 flex items-center gap-1.5 sm:self-end">
                <Info className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
                {t('profile.photoNote')}
              </p>
            </div>
          </section>

          <div role="note" className="alert alert-soft alert-info">
            <Info className="size-5" strokeWidth={1.75} aria-hidden="true" />
            <span>{t('profile.readOnly')}</span>
          </div>

          <ProfileCards profile={profile} />
          <NotificationsCard />
        </>
      )}

      <section className="card-soft p-5 sm:p-6 space-y-5">
        <div>
          <h3 className="font-display text-lg mb-2 flex items-center gap-2">
            <KeyRound className="size-5 text-primary" strokeWidth={1.75} aria-hidden="true" />
            {t('profile.password')}
          </h3>
          <p className="text-sm opacity-80 leading-relaxed">{t('profile.passwordNote')}</p>
        </div>
        <div className="brand-rule" />
        <a href={LEGACY_PORTAL_URL} className="btn btn-ghost btn-block justify-start">
          <ArrowUpRight className="size-5" strokeWidth={1.75} aria-hidden="true" />
          {t('app.backToLegacy')}
        </a>
        <LogoutButton className="btn-outline btn-error btn-block btn-lg sm:btn-md" />
      </section>
    </div>
  );
}

function ProfileCards({ profile }: { profile: Profile }) {
  const [showCpr, setShowCpr] = useState(false);
  const none = t('profile.none');

  return (
    <section className="grid gap-5 lg:grid-cols-2">
      <Card icon={IdCard} title={t('profile.personal')}>
        <Field label={t('profile.fullName')} wide>
          {profile.name}
        </Field>
        <Field label={t('profile.cpr')}>
          {profile.cpr ? (
            <span className="flex items-center gap-2">
              <span className="num ltr tracking-wider">{showCpr ? profile.cpr : maskCpr(profile.cpr)}</span>
              <button
                type="button"
                className="btn btn-ghost btn-xs btn-circle"
                aria-label={showCpr ? t('profile.hideCpr') : t('profile.showCpr')}
                aria-pressed={showCpr}
                onClick={() => setShowCpr((v) => !v)}
              >
                {showCpr ? (
                  <EyeOff className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
                ) : (
                  <Eye className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
                )}
              </button>
            </span>
          ) : (
            none
          )}
        </Field>
        <Field label={t('profile.dob')}>
          {profile.dob ? fmtDate(profile.dob) : none}
          {profile.age && <span className="opacity-60 text-sm num"> {t('profile.age', { n: profile.age })}</span>}
        </Field>
        <Field label={t('profile.degree')}>{profile.degree || none}</Field>
      </Card>

      <Card icon={Phone} title={t('profile.contact')}>
        <Field label={t('profile.mobile')}>
          <span className="num ltr">{profile.mobile || none}</span>
        </Field>
        <Field label={t('profile.mobile2')}>
          <span className="num ltr">{profile.mobile2 || none}</span>
        </Field>
        <Field label={t('profile.email')} wide>
          <span className="ltr">{profile.email || none}</span>
        </Field>
        <Field label={t('profile.address')} wide>
          {profile.address || none}
        </Field>
      </Card>

      <Card icon={GraduationCap} title={t('profile.academic')}>
        <Field label={t('profile.studentId')}>
          <span className="num">{profile.student_id}</span>
        </Field>
        <Field label={t('profile.registration')}>{profile.registration_at ? fmtDate(profile.registration_at) : none}</Field>
        <Field label={t('profile.iqraa')} wide>
          <span className="num ltr">{profile.iqraa_id_account || none}</span>
        </Field>
      </Card>
    </section>
  );
}

function Card({ icon: Icon, title, children }: { icon: LucideIcon; title: string; children: ReactNode }) {
  return (
    <div className="card-soft p-5 sm:p-6">
      <h3 className="font-display text-lg mb-4 flex items-center gap-2">
        <Icon className="size-5 text-primary" strokeWidth={1.75} aria-hidden="true" />
        {title}
      </h3>
      <dl className="grid sm:grid-cols-2 gap-x-6 gap-y-4">{children}</dl>
    </div>
  );
}

function Field({ label, wide, children }: { label: string; wide?: boolean; children: ReactNode }) {
  return (
    <div className={wide ? 'sm:col-span-2' : ''}>
      <dt className="text-[0.8125rem] opacity-60">{label}</dt>
      <dd className="font-medium">{children}</dd>
    </div>
  );
}
