import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

interface EmptyStateProps {
  icon: LucideIcon;
  title: string;
  body?: string;
  action?: ReactNode;
}

export function EmptyState({ icon: Icon, title, body, action }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center text-center py-10 px-6 gap-3">
      <div className="arch w-16 h-20 bg-base-200 grid place-items-center text-primary">
        <Icon className="size-7" strokeWidth={1.75} aria-hidden="true" />
      </div>
      <h2 className="font-display text-lg">{title}</h2>
      {body && <p className="text-sm opacity-70 max-w-sm">{body}</p>}
      {action}
    </div>
  );
}
