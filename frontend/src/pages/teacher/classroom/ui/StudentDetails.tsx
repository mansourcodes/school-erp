import { CalendarCheck } from 'lucide-react';
import { type ClassroomDetail, useTeacherClassroomStudentQuery } from '@/entities/classroom';
import { AttendanceCalendar } from '@/widgets/attendance-calendar';
import { t } from '@/shared/i18n';
import { StudentContact } from './StudentContact';

/** The student sheet's lower part: their calendar (my subjects) then contact + personal details. */
export function StudentDetails({
  classroom,
  studentId,
  onToast,
}: {
  classroom: ClassroomDetail;
  studentId: number;
  onToast: (message: string) => void;
}) {
  const { data, isLoading, isError } = useTeacherClassroomStudentQuery({ classroomId: classroom.id, studentId });

  if (isLoading) {
    return (
      <div className="space-y-3" aria-busy="true">
        <div className="skeleton h-72 w-full rounded-box" />
        <div className="skeleton h-40 w-full rounded-box" />
      </div>
    );
  }
  if (isError || !data) return null;

  return (
    <div className="space-y-4">
      {data.sessions.length ? (
        <AttendanceCalendar
          key={data.id}
          compact
          sessions={data.sessions}
          startDate={classroom.course.start_date}
          endDate={classroom.course.end_date}
        />
      ) : (
        <p className="rounded-box bg-base-200 p-4 text-sm opacity-70 flex items-center gap-2">
          <CalendarCheck className="size-4 shrink-0" strokeWidth={1.75} aria-hidden="true" />
          {t('teacher.studentNoSessions')}
        </p>
      )}
      <StudentContact student={data} onCopied={onToast} />
    </div>
  );
}
