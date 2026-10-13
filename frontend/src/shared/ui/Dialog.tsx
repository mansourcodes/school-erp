import { type ReactNode, useEffect, useRef } from 'react';

interface DialogProps {
  open: boolean;
  onClose: () => void;
  /** Accessible name: id of the dialog's heading. */
  labelledBy: string;
  closeLabel: string;
  children: ReactNode;
}

/**
 * Native <dialog>: a bottom sheet on phones, centred from `sm` (PLAN.md §2 phone patterns).
 * Never `alert()` / `confirm()`.
 */
export function Dialog({ open, onClose, labelledBy, closeLabel, children }: DialogProps) {
  const ref = useRef<HTMLDialogElement>(null);

  useEffect(() => {
    const dialog = ref.current;
    if (!dialog) return;
    if (open && !dialog.open) {
      // jsdom has no showModal(); fall back to the open attribute.
      if (typeof dialog.showModal === 'function') dialog.showModal();
      else dialog.setAttribute('open', '');
    } else if (!open && dialog.open) {
      if (typeof dialog.close === 'function') dialog.close();
      else dialog.removeAttribute('open');
    }
  }, [open]);

  return (
    <dialog ref={ref} className="modal modal-bottom sm:modal-middle" aria-labelledby={labelledBy} onClose={onClose}>
      <div className="modal-box p-0 overflow-hidden">{children}</div>
      <form method="dialog" className="modal-backdrop">
        <button type="submit">{closeLabel}</button>
      </form>
    </dialog>
  );
}
