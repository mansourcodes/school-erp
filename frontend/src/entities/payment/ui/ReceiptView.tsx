import { t } from '@/shared/i18n';
import { fmtDate, money } from '@/shared/lib';
import type { Payment } from '../model/types';

interface ReceiptViewProps {
  payment: Payment;
  studentName: string;
  studentNumber: string;
  courseName: string;
  /** id of the title, for the dialog's accessible name */
  titleId: string;
}

/** Same fields as the admin `payment_print_template` receipt. */
export function ReceiptView({ payment, studentName, studentNumber, courseName, titleId }: ReceiptViewProps) {
  return (
    <>
      <div className="hero-band bg-pattern p-5 flex items-center gap-3">
        <img src={`${import.meta.env.BASE_URL}logo-icon.svg`} className="h-10 brightness-0 invert" alt="" />
        <div>
          <p className="text-xs opacity-80">{t('app.centre')}</p>
          <h2 id={titleId} className="font-display text-xl">
            {t('receipt.title')}
          </h2>
        </div>
      </div>
      <dl className="grid grid-cols-2 gap-4 p-5 [&_dt]:text-sm [&_dt]:opacity-60 [&_dd]:font-medium">
        <div>
          <dt>{t('receipt.number')}</dt>
          <dd className="num">#{payment.id}</dd>
        </div>
        <div>
          <dt>{t('receipt.date')}</dt>
          <dd>{fmtDate(payment.date)}</dd>
        </div>
        <div className="col-span-2">
          <dt>{t('receipt.from')}</dt>
          <dd>{studentName}</dd>
        </div>
        <div>
          <dt>{t('receipt.studentNumber')}</dt>
          <dd className="num">{studentNumber}</dd>
        </div>
        <div>
          <dt>{t('receipt.amount')}</dt>
          <dd className="num text-lg">{money(payment.amount)}</dd>
        </div>
        <div className="col-span-2">
          <dt>{t('receipt.details')}</dt>
          <dd>
            {payment.type} — {courseName}
          </dd>
        </div>
      </dl>
    </>
  );
}
