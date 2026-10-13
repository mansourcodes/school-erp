/** GET /api/v1/me (MeResource). Teachers join in Phase 3 (PLAN.md §8). */
export interface Viewer {
  role: 'student';
  id: number;
  name: string;
  first_name: string;
  student_id: string;
  photo_url: string | null;
}

export type ViewerRole = Viewer['role'];

export function viewerInitial(viewer: Pick<Viewer, 'first_name' | 'name'>): string {
  return Array.from(viewer.first_name || viewer.name)[0] ?? '';
}
