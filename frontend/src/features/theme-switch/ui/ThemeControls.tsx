import { type LucideIcon, Monitor, Moon, Sun } from 'lucide-react';
import { t } from '@/shared/i18n';
import { useAppDispatch, useAppSelector } from '@/shared/lib';
import { selectThemeMode, THEME_MODES, type ThemeMode, themeModeCycled, themeModeSet } from '../model/theme-slice';

const ICONS: Record<ThemeMode, LucideIcon> = { system: Monitor, light: Sun, dark: Moon };

/** Compact top-bar button: system → light → dark. */
export function ThemeCycleButton({ className = '' }: { className?: string }) {
  const mode = useAppSelector(selectThemeMode);
  const dispatch = useAppDispatch();
  const Icon = ICONS[mode];
  const label = t('theme.current', { mode: t(`theme.${mode}`) });

  return (
    <button
      type="button"
      className={`btn btn-ghost btn-circle ${className}`}
      onClick={() => dispatch(themeModeCycled())}
      aria-label={label}
      title={label}
    >
      <Icon className="size-5" strokeWidth={1.75} aria-hidden="true" />
    </button>
  );
}

/** Three-way segmented control (sidebar, profile). */
export function ThemeSegment() {
  const mode = useAppSelector(selectThemeMode);
  const dispatch = useAppDispatch();

  return (
    <div className="join w-full" role="group" aria-label={t('theme.label')}>
      {THEME_MODES.map((m) => {
        const Icon = ICONS[m];
        return (
          <button
            key={m}
            type="button"
            className={`btn btn-sm join-item flex-1 ${m === mode ? 'btn-active' : ''}`}
            aria-pressed={m === mode}
            onClick={() => dispatch(themeModeSet(m))}
          >
            <Icon className="size-4" strokeWidth={1.75} aria-hidden="true" />
            <span className="sr-only">{t(`theme.${m}`)}</span>
          </button>
        );
      })}
    </div>
  );
}
