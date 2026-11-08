export {
  classroomApi,
  useTeacherClassroomQuery,
  useTeacherClassroomsQuery,
  useTeacherClassroomStudentQuery,
  useTeacherStatsQuery,
} from './api/classroom-api';
export {
  type ClassroomDetail,
  type ClassroomSlot,
  type ClassroomStudent,
  type ClassroomStudentDetail,
  LOW_RATE,
  type TeacherClassroom,
  type TeacherStats,
} from './model/types';
export { ClassroomCard } from './ui/ClassroomCard';
