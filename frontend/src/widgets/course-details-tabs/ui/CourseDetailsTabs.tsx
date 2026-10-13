import { Award, Calendar, ChevronDown, Info, type LucideIcon, Receipt } from 'lucide-react';
import { useState } from 'react';
import { useLocation, useNavigate } from 'react-router';
import { CertificateLink } from '@/features/download-certificate';
import { ReceiptDialog } from '@/features/print-receipt';
import { type CourseDetail, CourseMeta, Timetable } from '@/entities/course';
import { MarksList } from '@/entities/mark';
import { type Payment, PaymentList } from '@/entities/payment';
import type { Viewer } from '@/entities/viewer';
import { t } from '@/shared/i18n';

export type CourseTab = 'timetable' | 'payments' | 'marks';

const TABS: { id: CourseTab; label: string; icon: LucideIcon }[] = [
  { id: 'timetable', label: 'course.tabTimetable', icon: Calendar },
  { id: 'payments', label: 'course.tabPayments', icon: Receipt },
  { id: 'marks', label: 'course.tabMarks', icon: Award },
];

/** The active tab lives in the URL hash (#payments, #marks), so it can be linked to. */
export function useCourseTab(): [CourseTab, (tab: CourseTab) => void] {
  const { hash, pathname, search } = useLocation();
  const navigate = useNavigate();
  const current = TABS.find((tab) => `#${tab.id}` === hash)?.id ?? 'timetable';
  const select = (tab: CourseTab) =>
    navigate({ pathname, search, hash: tab === 'timetable' ? '' : tab }, { replace: true, preventScrollReset: true });
  return [current, select];
}

interface CourseDetailsTabsProps {
  course: CourseDetail;
  viewer: Viewer;
  /** 0–6; highlights today in the timetable (current course only). */
  today?: number;
}

export function CourseDetailsTabs({ course, viewer, today }: CourseDetailsTabsProps) {
  const [tab, selectTab] = useCourseTab();
  const [receipt, setReceipt] = useState<Payment | null>(null);
  // Course info is secondary on phones: collapsed there, open from `sm` up.
  const [metaOpen, setMetaOpen] = useState(() => window.matchMedia?.('(min-width: 640px)').matches ?? true);

  return (
    <section className="card-soft" id="course-details">
      <div
        role="tablist"
        aria-label={t('course.tabsLabel')}
        className="sticky top-[calc(3.5rem+env(safe-area-inset-top))] lg:top-16 z-20 bg-base-100 rounded-t-box border-b border-base-300 grid grid-cols-3"
      >
        {TABS.map(({ id, label, icon: Icon }) => (
          <button
            key={id}
            type="button"
            role="tab"
            id={`tab-${id}`}
            aria-selected={tab === id}
            aria-controls={`panel-${id}`}
            tabIndex={tab === id ? 0 : -1}
            onClick={() => selectTab(id)}
            className={`tab h-13 sm:h-14 gap-1 sm:gap-1.5 px-1 flex-nowrap whitespace-nowrap text-sm sm:text-base ${
              tab === id ? 'tab-active text-primary font-semibold' : ''
            }`}
          >
            <Icon className="size-4" strokeWidth={1.75} aria-hidden="true" />
            {t(label)}
          </button>
        ))}
      </div>

      <div className="p-4 sm:p-6">
        {tab === 'timetable' && (
          <div role="tabpanel" id="panel-timetable" aria-labelledby="tab-timetable" className="space-y-5">
            <Timetable curricula={course.curricula} today={today} />
            <details
              className="group rounded-box bg-base-200/60 sm:bg-transparent"
              open={metaOpen}
              onToggle={(event) => setMetaOpen(event.currentTarget.open)}
            >
              <summary className="list-none cursor-pointer flex items-center justify-between p-3 sm:p-0 sm:mb-3 font-display min-h-12">
                <span className="flex items-center gap-2">
                  <Info className="size-4 text-primary" strokeWidth={1.75} aria-hidden="true" />
                  {t('course.info')}
                </span>
                <ChevronDown
                  className="size-4 opacity-60 sm:hidden group-open:rotate-180 transition-transform"
                  strokeWidth={1.75}
                  aria-hidden="true"
                />
              </summary>
              <div className="px-3 pb-4 sm:p-0">
                <CourseMeta course={course} />
              </div>
            </details>
          </div>
        )}

        {tab === 'payments' && (
          <div role="tabpanel" id="panel-payments" aria-labelledby="tab-payments">
            <PaymentList payments={course.payments} onSelect={setReceipt} />
          </div>
        )}

        {tab === 'marks' && (
          <div role="tabpanel" id="panel-marks" aria-labelledby="tab-marks" className="space-y-5">
            <MarksList
              marks={course.marks}
              published={course.marks_published}
              totalMark={course.total_mark}
              finalGrade={course.final_grade}
            />
            <CertificateLink courseId={course.id} published={course.marks_published} />
          </div>
        )}
      </div>

      <ReceiptDialog
        payment={receipt}
        studentName={viewer.name}
        studentNumber={viewer.student_id}
        courseName={course.long_name}
        onClose={() => setReceipt(null)}
      />
    </section>
  );
}
