export interface MessageClassroom {
  id: number;
  number: string | null;
  name: string | null;
}

/** GET /student/messages (PLAN.md §7.2): read-only inbox. */
export interface InboxMessage {
  id: number;
  teacher: string | null;
  teacher_photo_url: string | null;
  classroom: MessageClassroom | null;
  body: string;
  sent_at: string;
  read_at: string | null;
}

/** GET /teacher/messages: a sent message with read progress (PLAN.md §8 screen 7). */
export interface SentMessage {
  id: number;
  classroom: MessageClassroom | null;
  body: string;
  sent_at: string;
  recipients_count: number;
  read_count: number;
  recipients: { id: number; name: string | null; photo_url: string | null; read_at: string | null }[];
}

export interface Page<T> {
  data: T[];
  meta: { current_page: number; last_page: number };
}

/** Text limit (server: body ≤ 500). */
export const MESSAGE_MAX = 500;
