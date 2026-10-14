import { useParams } from 'react-router';
import { useRosterQuery } from '@/entities/class-session';
import { RosterEditor } from '@/widgets/roster-editor';
import { ErrorCard, LoadingBlocks } from '@/shared/ui';

/** /teacher/attendance/:code, a task screen (back arrow, no dock). */
export function TeacherRosterPage() {
  const code = decodeURIComponent(useParams().code ?? '');
  // `currentData`, not `data`: after previous / next, `data` still holds the old session until the new one arrives,
  // so the skeletons show instead of stale rows.
  const { currentData: roster, isFetching, isError, refetch } = useRosterQuery(code, { skip: !code });

  if (!roster && isFetching) return <LoadingBlocks heights={['h-36', 'h-24', 'h-32', 'h-32', 'h-32']} />;
  if (isError || !roster) return <ErrorCard onRetry={refetch} />;

  // A new session (previous / next) starts a fresh draft.
  return <RosterEditor key={roster.session.code} roster={roster} />;
}
