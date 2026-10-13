import { useSearchParams } from 'react-router';
import { useArchivedCoursesQuery, useCurrentCourseQuery } from '@/entities/course';

/**
 * The course shown on the attendance and stats screens: `?course=` (from archive / stats links),
 * else the current course, else the newest archived one.
 */
export function useSelectedCourse() {
  const [params, setParams] = useSearchParams();
  const current = useCurrentCourseQuery();
  const archived = useArchivedCoursesQuery();

  const fromUrl = Number(params.get('course')) || undefined;
  const courseId = fromUrl ?? current.data?.id ?? archived.data?.[0]?.id;

  const known = courseId === current.data?.id ? current.data : archived.data?.find((c) => c.id === courseId);

  const setCourse = (id: number) => {
    const next = new URLSearchParams(params);
    next.set('course', String(id));
    next.delete('date');
    setParams(next, { replace: true });
  };

  return {
    courseId,
    /** Dates of the selected course, when it is one of the listed courses. */
    course: known ?? null,
    current: current.data,
    archived: archived.data ?? [],
    isLoading: current.isLoading || archived.isLoading,
    isError: current.isError && archived.isError,
    setCourse,
  };
}
