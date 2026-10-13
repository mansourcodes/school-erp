/** GET /api/v1/me: a student (MeResource) or a teacher (TeacherMeResource), PLAN.md §8. */
export interface StudentViewer {
  role: 'student';
  id: number;
  name: string;
  first_name: string;
  student_id: string;
  photo_url: string | null;
}

export interface TeacherViewer {
  role: 'teacher';
  id: number;
  name: string;
  first_name: string;
  email: string;
  photo_url: string | null;
}

export type Viewer = StudentViewer | TeacherViewer;

export type ViewerRole = Viewer['role'];

export function viewerInitial(viewer: Pick<Viewer, 'first_name' | 'name'>): string {
  return Array.from(viewer.first_name || viewer.name)[0] ?? '';
}
