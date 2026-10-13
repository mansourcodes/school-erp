import { Printer } from 'lucide-react';
import { type Payment, ReceiptView } from '@/entities/payment';
import { t } from '@/shared/i18n';
import { Dialog } from '@/shared/ui';

interface ReceiptDialogProps {
  payment: Payment | null;
  studentName: string;
  studentNumber: string;
  courseName: string;
  onClose: () => void;
}

/** Bottom sheet with the receipt; "طباعة" prints only the receipt (`.print-area`). */
export function ReceiptDialog({ payment, studentName, studentNumber, courseName, onClose }: ReceiptDialogProps) {
  return (
    <Dialog open={payment !== null} onClose={onClose} labelledBy="receipt-title" closeLabel={t('receipt.close')}>
      {payment && (
        <div className="print-area">
          <ReceiptView
            payment={payment}
            studentName={studentName}
            studentNumber={studentNumber}
            courseName={courseName}
            titleId="receipt-title"
          />
          <div className="flex gap-2 p-5 pt-0 print:hidden">
            <button type="button" className="btn btn-primary flex-1" onClick={() => window.print()}>
              <Printer className="size-4" strokeWidth={1.75} aria-hidden="true" />
              {t('receipt.print')}
            </button>
            <button type="button" className="btn flex-1" onClick={onClose}>
              {t('receipt.close')}
            </button>
          </div>
        </div>
      )}
    </Dialog>
  );
}
