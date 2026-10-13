/**
 * One error shape for the UI, from the API envelope (PLAN.md §7):
 * `{ message, errors?, retry_after?, attempts_left? }` with 401/403/404/419/422/429.
 */
export interface ApiError {
  status: number | 'offline' | 'unknown';
  message?: string;
  errors?: Record<string, string[]>;
  retryAfter?: number;
  attemptsLeft?: number;
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}

function asNumber(value: unknown): number | undefined {
  return typeof value === 'number' && Number.isFinite(value) ? value : undefined;
}

export function toApiError(error: unknown): ApiError {
  if (!isRecord(error) || !('status' in error)) {
    return { status: 'unknown' };
  }
  if (error.status === 'FETCH_ERROR') {
    return { status: 'offline' };
  }
  if (typeof error.status !== 'number') {
    return { status: 'unknown' };
  }

  const body = isRecord(error.data) ? error.data : {};
  return {
    status: error.status,
    message: typeof body.message === 'string' ? body.message : undefined,
    errors: isRecord(body.errors) ? (body.errors as Record<string, string[]>) : undefined,
    retryAfter: asNumber(body.retry_after),
    attemptsLeft: asNumber(body.attempts_left),
  };
}
