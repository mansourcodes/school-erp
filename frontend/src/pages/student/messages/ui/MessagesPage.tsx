import { InboxList } from '@/widgets/inbox-list';

/** /messages (PLAN.md §3.7): read-only inbox; `#id` from a notification scrolls to that message. */
export function MessagesPage() {
  return <InboxList />;
}
