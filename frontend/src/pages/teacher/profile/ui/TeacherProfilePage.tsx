import { AtSign, Camera, KeyRound, School } from 'lucide-react';
import { LogoutButton } from '@/features/auth/logout';
import { ChangePasswordForm } from '@/features/change-password';
import { ChangeUsernameForm } from '@/features/change-username';
import { TeacherPhotoActions } from '@/features/manage-teacher-photo';
import { useTeacherClassroomsQuery } from '@/entities/classroom';
import { useMeQuery, ViewerAvatar } from '@/entities/viewer';
import { t } from '@/shared/i18n';
import { LoadingBlocks } from '@/shared/ui';

/**
 * /teacher/profile (PLAN.md §8 screen 9): name, username, email, assigned classrooms, own photo, username and
 * password change, logout.
 */
export function TeacherProfilePage() {
  const { data: viewer } = useMeQuery();
  const { data: classrooms } = useTeacherClassroomsQuery();

  if (!viewer || viewer.role !== 'teacher') return <LoadingBlocks heights={['h-40', 'h-40']} />;

  return (
    <div className="space-y-5">
      <section className="card-soft overflow-hidden">
        <div className="hero-band bg-pattern h-20" />
        <div className="px-5 pb-6 -mt-12 flex items-end gap-4">
          <ViewerAvatar viewer={viewer} className="w-20 h-24" textClassName="text-3xl" />
          <div className="min-w-0">
            <h2 className="font-display text-xl">{viewer.name}</h2>
            <p className="text-sm opacity-70">{t('teacher.role')}</p>
          </div>
        </div>
      </section>

      <section className="card-soft p-5 sm:p-6">
        <dl className="grid sm:grid-cols-2 gap-4 [&_dt]:text-[0.8125rem] [&_dt]:opacity-60 [&_dd]:font-medium">
          <div>
            <dt>{t('teacher.username')}</dt>
            <dd className="ltr">{viewer.username}</dd>
          </div>
          <div>
            <dt>{t('teacher.email')}</dt>
            <dd className="ltr">{viewer.email || '—'}</dd>
          </div>
          <div>
            <dt className="flex items-center gap-1">
              <School className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
              {t('teacher.assigned')}
            </dt>
            <dd className="num">
              {classrooms?.length ? classrooms.map((c) => t('teacher.room', { room: c.number ?? '' })).join('، ') : '—'}
            </dd>
          </div>
        </dl>
      </section>

      <section className="card-soft p-5 sm:p-6">
        <h3 className="font-display text-lg mb-2 flex items-center gap-2">
          <Camera className="size-5 text-primary" strokeWidth={1.75} aria-hidden="true" />
          {t('teacher.ownPhoto')}
        </h3>
        <TeacherPhotoActions hasPhoto={viewer.photo_url !== null} />
      </section>

      <section className="card-soft p-5 sm:p-6">
        <h3 className="font-display text-lg mb-2 flex items-center gap-2">
          <AtSign className="size-5 text-primary" strokeWidth={1.75} aria-hidden="true" />
          {t('changeUsername.title')}
        </h3>
        <p className="text-sm opacity-80 mb-4">{t('changeUsername.note')}</p>
        <ChangeUsernameForm username={viewer.username} />
      </section>

      <section className="card-soft p-5 sm:p-6 space-y-5">
        <div>
          <h3 className="font-display text-lg mb-2 flex items-center gap-2">
            <KeyRound className="size-5 text-primary" strokeWidth={1.75} aria-hidden="true" />
            {t('changePassword.title')}
          </h3>
          <p className="text-sm opacity-80 mb-4">{t('changePassword.note')}</p>
          <ChangePasswordForm />
        </div>
        <div className="brand-rule" />
        <LogoutButton className="btn-outline btn-error btn-block btn-lg sm:btn-md" />
      </section>
    </div>
  );
}
