import { ChevronLeft, Receipt, Wallet } from 'lucide-react';
import { t } from '@/shared/i18n';
import { fmtDate, money } from '@/shared/lib';
import { EmptyState } from '@/shared/ui';
import { type Payment, totalPaid } from '../model/types';

interface PaymentListProps {
  payments: Payment[];
  /** Whole row is the tap target (PLAN.md §2 touch rules). */
  onSelect: (payment: Payment) => void;
}

export function PaymentList({ payments, onSelect }: PaymentListProps) {
  if (payments.length === 0) {
    return <EmptyState icon={Receipt} title={t('payments.noneTitle')} body={t('payments.noneBody')} />;
  }

  return (
    <>
      <div className="flex items-center justify-between gap-4 p-4 rounded-box bg-primary/10 border border-primary/20 mb-4">
        <div className="flex items-center gap-3">
          <div className="size-10 rounded-full bg-primary text-primary-content grid place-items-center">
            <Wallet className="size-5" strokeWidth={1.75} aria-hidden="true" />
          </div>
          <div>
            <p className="text-sm opacity-70">{t('payments.total')}</p>
            <p className="text-xl font-semibold num">{money(totalPaid(payments))}</p>
          </div>
        </div>
        <span className="text-sm opacity-70 num">{t('payments.count', { n: payments.length })}</span>
      </div>

      <ul className="divide-y divide-base-300 -mx-2">
        {payments.map((payment) => (
          <li key={payment.id}>
            <button
              type="button"
              className="w-full min-h-16 px-2 py-3 flex items-center gap-3 text-start rounded-field active:bg-base-200 hover:bg-base-200/60 transition-colors"
              aria-label={t('payments.open', { id: payment.id })}
              onClick={() => onSelect(payment)}
            >
              <div className="size-10 rounded-xl bg-base-200 grid place-items-center text-primary shrink-0">
                <Receipt className="size-5" strokeWidth={1.75} aria-hidden="true" />
              </div>
              <div className="flex-1 min-w-0">
                <p className="font-medium">{payment.type}</p>
                <p className="text-sm opacity-70 truncate">
                  <span className="num">#{payment.id}</span> ·{' '}
                  {fmtDate(payment.date, { day: 'numeric', month: 'short', year: 'numeric' })} · {payment.source}
                </p>
              </div>
              <p className="font-semibold num whitespace-nowrap">{money(payment.amount)}</p>
              <ChevronLeft className="size-4 opacity-40 shrink-0" strokeWidth={1.75} aria-hidden="true" />
            </button>
          </li>
        ))}
      </ul>
    </>
  );
}
