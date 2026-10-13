export interface Payment {
  id: number;
  /** YYYY-MM-DD */
  date: string;
  /** Bahraini dinar */
  amount: number;
  type: string;
  source: string;
}

export function totalPaid(payments: Payment[]): number {
  return payments.reduce((sum, p) => sum + p.amount, 0);
}
