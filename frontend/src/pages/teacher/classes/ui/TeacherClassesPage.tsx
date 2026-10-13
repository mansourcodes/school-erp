import { School } from 'lucide-react';
import { ClassroomCard, useTeacherClassroomsQuery } from '@/entities/classroom';
import { t } from '@/shared/i18n';
import { EmptyState, ErrorCard, LoadingBlocks } from '@/shared/ui';

/** /teacher/classes (PLAN.md §8 screen 4): my classrooms of the current course. */
export function TeacherClassesPage() {
  const { data: classrooms, isLoading, isError, refetch } = useTeacherClassroomsQuery();

  if (isLoading) return <LoadingBlocks heights={['h-40', 'h-40']} />;
  if (isError || !classrooms) return <ErrorCard onRetry={refetch} />;
  if (classrooms.length === 0) {
    return (
      <div className="card-soft">
        <EmptyState icon={School} title={t('teacher.noClasses')} body={t('teacher.noClassesBody')} />
      </div>
    );
  }

  return (
    <div className="grid gap-3 md:grid-cols-2">
      {classrooms.map((classroom) => (
        <ClassroomCard key={classroom.id} classroom={classroom} />
      ))}
    </div>
  );
}
