import { Archive, BookOpen, CalendarCheck } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useLocation, useNavigate, useSearchParams } from 'react-router';
import { LoginForm } from '@/features/auth/login';
import { JUST_LOGGED_IN_KEY } from '@/features/install-app';
import { PasswordHelpDialog } from '@/features/password-help';
import { useBranding } from '@/entities/branding';
import { BASE_PATH, homeOf, routes } from '@/shared/config';
import { t } from '@/shared/i18n';

const BRAND_POINTS = [
  { icon: BookOpen, key: 'login.brandPoint1' },
  { icon: CalendarCheck, key: 'login.brandPoint2' },
  { icon: Archive, key: 'login.brandPoint3' },
];

export function LoginPage() {
  const [params] = useSearchParams();
  const navigate = useNavigate();
  const location = useLocation();
  const from = (location.state as { from?: string } | null)?.from ?? routes.home;
  const [helpOpen, setHelpOpen] = useState(false);
  const branding = useBranding();

  useEffect(() => {
    document.title = branding.name ? `${t('login.title')} · ${branding.name}` : t('login.title');
  }, [branding.name]);

  return (
    <main className="min-h-dvh grid lg:grid-cols-[1.05fr_1fr]">
      {/* Brand panel: start side (right in RTL) */}
      <section className="hero-band bg-pattern relative flex flex-col justify-between px-5 pt-[calc(1.25rem+env(safe-area-inset-top))] pb-20 lg:p-14 overflow-hidden">
        <div className="flex items-center justify-between">
          <img src={`${BASE_PATH}logo-full.svg`} alt={branding.centre} className="h-12 lg:h-20 w-auto brightness-0 invert" />
        </div>
        <div className="hidden lg:block max-w-md">
          <div className="brand-rule w-24 mb-6" />
          <h2 className="font-display text-4xl leading-snug mb-4">{t('login.brandTitle')}</h2>
          <p className="text-lg opacity-85 leading-relaxed">{t('login.brandBody')}</p>
          <ul className="mt-8 space-y-3 text-sm opacity-90">
            {BRAND_POINTS.map(({ icon: Icon, key }) => (
              <li key={key} className="flex items-center gap-3">
                <Icon className="size-5" strokeWidth={1.75} aria-hidden="true" />
                {t(key)}
              </li>
            ))}
          </ul>
        </div>
        <p className="hidden lg:block text-xs opacity-70">© {branding.centre}</p>
        <div className="arch absolute -bottom-10 -left-10 w-72 h-96 bg-white/10 hidden lg:block" aria-hidden="true" />
      </section>

      {/* Form panel: overlaps the brand band on phones */}
      <section className="flex items-start lg:items-center justify-center px-3 sm:px-4 -mt-14 lg:mt-0 pb-[calc(2rem+env(safe-area-inset-bottom))] lg:p-12 relative">
        <div className="w-full max-w-md">
          <LoginForm
            expired={params.get('expired') === '1'}
            onSuccess={(viewer) => {
              try {
                sessionStorage.setItem(JUST_LOGGED_IN_KEY, '1');
              } catch {
                // Private mode: no install banner right after login.
              }
              // Back to where the session expired, if that page belongs to this role; else the role's home.
              const ownsFrom = viewer.role === 'teacher' ? from.startsWith('/teacher') : !from.startsWith('/teacher');
              navigate(from !== routes.home && ownsFrom ? from : homeOf(viewer.role), { replace: true });
            }}
            onForgotPassword={() => setHelpOpen(true)}
          />
        </div>
      </section>

      <PasswordHelpDialog open={helpOpen} onClose={() => setHelpOpen(false)} />
    </main>
  );
}
