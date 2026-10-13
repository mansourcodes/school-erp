import { Camera, ChevronLeft, Search } from 'lucide-react';
import { useState } from 'react';
import { PhotoActions } from '@/features/manage-student-photo';
import { type ClassroomStudent, LOW_RATE } from '@/entities/classroom';
import { StudentPhoto } from '@/entities/student';
import { t } from '@/shared/i18n';
import { pct } from '@/shared/lib';
import { Dialog } from '@/shared/ui';

/** "الطلاب" tab of a classroom (PLAN.md §8 screen 5): search, rows, and the student sheet with photo actions. */
export function ClassroomStudents({ classroomId, students }: { classroomId: number; students: ClassroomStudent[] }) {
  const [query, setQuery] = useState('');
  const [openId, setOpenId] = useState<number | null>(null);
  const [toast, setToast] = useState<string | null>(null);
  const open = students.find((s) => s.id === openId) ?? null;
  const shown = students.filter((s) => !query.trim() || s.name.includes(query.trim()) || s.student_id.includes(query.trim()));

  return (
    <div className="space-y-3">
      <label className="input w-full">
        <Search className="size-4 opacity-60" strokeWidth={1.75} aria-hidden="true" />
        <input type="search" placeholder={t('teacher.search')} aria-label={t('teacher.searchLabel')} value={query} onChange={(e) => setQuery(e.target.value)} />
      </label>

      <ul className="divide-y divide-base-300 -mx-2">
        {shown.map((student) => (
          <li key={student.id}>
            <button
              type="button"
              className="w-full min-h-16 px-2 py-2.5 flex items-center gap-3 text-start rounded-field active:bg-base-200 hover:bg-base-200/60"
              onClick={() => setOpenId(student.id)}
            >
              <StudentPhoto name={student.name} photoUrl={student.photo_url} />
              <div className="min-w-0 flex-1">
                <p className="font-medium leading-snug truncate">{student.name}</p>
                <p className="text-xs opacity-60 num flex gap-2">
                  <span>{student.student_id}</span>
                  {!student.photo_url && (
                    <span className="inline-flex items-center gap-1">
                      <Camera className="size-3" strokeWidth={1.75} aria-hidden="true" />
                      {t('teacher.noPhoto')}
                    </span>
                  )}
                </p>
              </div>
              <div className="text-end text-xs num shrink-0">
                <p className={student.rate !== null && student.rate < LOW_RATE ? 'text-error font-semibold' : ''}>
                  {student.rate === null ? '—' : pct(student.rate)}
                </p>
                <p className="opacity-60">{t('teacher.absences', { n: student.absences })}</p>
              </div>
              <ChevronLeft className="size-4 opacity-40 shrink-0" strokeWidth={1.75} aria-hidden="true" />
            </button>
          </li>
        ))}
      </ul>

      <Dialog open={open !== null} onClose={() => setOpenId(null)} labelledBy="student-sheet-title" closeLabel={t('pwa.close')}>
        {open && (
          <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))] space-y-4">
            <div className="sheet-grip" />
            <div className="flex flex-col items-center text-center gap-2">
              <StudentPhoto name={open.name} photoUrl={open.photo_url} className="w-32 h-40" textClassName="text-5xl" />
              <h2 id="student-sheet-title" className="font-display text-lg">
                {open.name}
              </h2>
              <p className="text-xs opacity-60 num">{open.student_id}</p>
            </div>
            <div className="grid grid-cols-3 gap-2 text-center num">
              <div className="rounded-box bg-base-200 p-2">
                <p className="text-lg font-semibold">{open.rate === null ? '—' : pct(open.rate)}</p>
                <p className="text-xs opacity-70">{t('attendance.rate')}</p>
              </div>
              <div className="rounded-box bg-base-200 p-2">
                <p className="text-lg font-semibold">{open.late}</p>
                <p className="text-xs opacity-70">{t('stats.late')}</p>
              </div>
              <div className="rounded-box bg-base-200 p-2">
                <p className="text-lg font-semibold">{open.absences}</p>
                <p className="text-xs opacity-70">{t('stats.absent')}</p>
              </div>
            </div>
            <PhotoActions
              classroomId={classroomId}
              studentId={open.id}
              hasPhoto={!!open.photo_url}
              onDone={(message) => {
                setToast(message);
                window.setTimeout(() => setToast(null), 3000);
              }}
            />
          </div>
        )}
      </Dialog>

      {toast && (
        <div className="toast toast-top toast-center z-[80]">
          <div role="status" className="alert alert-success shadow-lg">
            <span>{toast}</span>
          </div>
        </div>
      )}
    </div>
  );
}
