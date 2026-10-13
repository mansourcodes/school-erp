import { Calendar, CircleCheck, CircleX, Clock, type LucideIcon, Table2 } from 'lucide-react';
import { ATTENDANCE_STATES, type AttendanceStats, RECORDED_STATES, StatusPip } from '@/entities/attendance';
import { t } from '@/shared/i18n';
import { fmtDate, pct } from '@/shared/lib';

interface AttendanceSummaryProps {
  stats: AttendanceStats;
  startDate: string | null;
}

/** Hero figure + meter, KPI tiles, one composition bar (PLAN.md §2 Charts, §3.5). */
export function AttendanceSummary({ stats, startDate }: AttendanceSummaryProps) {
  const { counts, recorded } = stats;
  const rate = stats.rate ?? 0;
  const share = (n: number) => (recorded ? pct((n / recorded) * 100) : pct(0));
  const parts = RECORDED_STATES.filter((state) => counts[state] > 0);

  return (
    <div className="space-y-5">
      <section className="grid gap-4 lg:grid-cols-[1.1fr_2fr]">
        <div className="card-soft p-4 sm:p-6">
          <p className="text-sm opacity-70">{t('stats.overall')}</p>
          <p className="text-5xl sm:text-6xl font-semibold mt-1 tracking-tight num">{pct(rate)}</p>
          <div
            className="bar-track mt-4"
            role="meter"
            aria-label={t('attendance.rate')}
            aria-valuemin={0}
            aria-valuemax={100}
            aria-valuenow={Math.round(rate)}
          >
            <div className="bar-fill" style={{ width: `${rate}%` }} />
          </div>
          <p className="text-sm opacity-70 mt-3">
            {t('stats.punctualLine', { pct: pct(stats.punctual ?? 0), n: recorded })}
            {counts.unrecorded > 0 && t('stats.unrecordedLine', { n: counts.unrecorded })}
          </p>
        </div>

        <div className="grid grid-cols-2 gap-3 sm:gap-4">
          <Tile
            icon={Calendar}
            label={t('stats.recorded')}
            value={recorded}
            sub={startDate ? t('stats.since', { date: fmtDate(startDate, { day: 'numeric', month: 'long' }) }) : ''}
          />
          <Tile icon={CircleCheck} label={t('stats.onTime')} value={counts.present} sub={t('stats.ofSessions', { pct: pct(stats.punctual ?? 0) })} />
          <Tile
            icon={Clock}
            label={t('stats.late')}
            value={counts.late + counts.late_excused}
            sub={t('stats.excusedPart', { n: counts.late_excused })}
          />
          <Tile
            icon={CircleX}
            label={t('stats.absent')}
            value={counts.absent + counts.absent_excused}
            sub={t('stats.excusedPart', { n: counts.absent_excused })}
          />
        </div>
      </section>

      <section className="card-soft p-4 sm:p-6">
        <header className="mb-4">
          <h2 className="font-display text-lg">{t('stats.composition')}</h2>
          <p className="text-sm opacity-60">{t('stats.compositionSub')}</p>
        </header>
        <div
          className="stack"
          role="img"
          aria-label={parts.map((state) => `${t(`attendanceState.${state}`)} ${counts[state]}`).join('، ')}
        >
          {parts.map((state) => (
            <span
              key={state}
              className={`${ATTENDANCE_STATES[state].cls} ${ATTENDANCE_STATES[state].excused ? 'hatch' : ''}`}
              style={{ flex: `${counts[state]} 1 0` }}
              title={`${t(`attendanceState.${state}`)}: ${t('stats.sessionsCount', { n: counts[state] })} · ${share(counts[state])}`}
            />
          ))}
        </div>
        <ul className="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4">
          {RECORDED_STATES.map((state) => (
            <li key={state} className="flex items-center gap-2">
              <StatusPip state={state} />
              <div className="leading-tight">
                <p className="text-sm">{t(`attendanceState.${state}`)}</p>
                <p className="text-xs opacity-60 num">
                  {counts[state]} · {share(counts[state])}
                </p>
              </div>
            </li>
          ))}
        </ul>
        <details className="mt-4 text-sm">
          <summary className="cursor-pointer opacity-70 inline-flex items-center gap-1.5">
            <Table2 className="size-4" strokeWidth={1.75} aria-hidden="true" />
            {t('stats.asTable')}
          </summary>
          <div className="overflow-x-auto mt-2">
            <table className="table table-sm">
              <thead>
                <tr>
                  <th>{t('stats.state')}</th>
                  <th className="text-end">{t('stats.sessions')}</th>
                  <th className="text-end">{t('stats.share')}</th>
                </tr>
              </thead>
              <tbody>
                {RECORDED_STATES.map((state) => (
                  <tr key={state}>
                    <td>{t(`attendanceState.${state}`)}</td>
                    <td className="text-end num">{counts[state]}</td>
                    <td className="text-end num">{share(counts[state])}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </details>
      </section>
    </div>
  );
}

function Tile({ icon: Icon, label, value, sub }: { icon: LucideIcon; label: string; value: number; sub: string }) {
  return (
    <div className="card-soft p-3.5 sm:p-4">
      <p className="text-xs sm:text-sm opacity-70 flex items-center gap-1.5">
        <Icon className="size-4 text-primary" strokeWidth={1.75} aria-hidden="true" />
        {label}
      </p>
      <p className="text-2xl sm:text-3xl font-semibold mt-1.5 num">{value}</p>
      {sub && <p className="text-xs opacity-60 mt-1">{sub}</p>}
    </div>
  );
}
