import { toApiError } from './errors';

describe('toApiError', () => {
  it('maps the login failure envelope', () => {
    expect(
      toApiError({ status: 422, data: { message: 'm', errors: { cpr: ['m'] }, attempts_left: 3 } }),
    ).toEqual({ status: 422, message: 'm', errors: { cpr: ['m'] }, retryAfter: undefined, attemptsLeft: 3 });
  });

  it('maps a lockout', () => {
    expect(toApiError({ status: 429, data: { message: 'm', retry_after: 900 } })).toMatchObject({
      status: 429,
      retryAfter: 900,
    });
  });

  it('treats fetch failures as offline', () => {
    expect(toApiError({ status: 'FETCH_ERROR', error: 'TypeError' })).toEqual({ status: 'offline' });
  });

  it('survives anything else', () => {
    expect(toApiError(new Error('boom'))).toEqual({ status: 'unknown' });
    expect(toApiError({ status: 500, data: 'html page' })).toMatchObject({ status: 500, message: undefined });
  });
});
