import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { viewerApi } from '@/entities/viewer';
import { demoViewer, server } from '../../../test/server';
import { renderApp } from '../../../test/render-app';

const signedIn = () => http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer }));

async function fillAndSubmit(cpr = '901234567', password = 'secret-pass') {
  const user = userEvent.setup();
  await user.type(await screen.findByLabelText('الرقم الشخصي (CPR)'), cpr);
  await user.type(screen.getByLabelText('كلمة المرور'), password);
  await user.click(screen.getByRole('button', { name: 'دخول' }));
  return user;
}

describe('auth flow', () => {
  it('sends signed-out visitors to the login screen', async () => {
    const { router } = renderApp('/profile');

    expect(await screen.findByRole('heading', { name: 'تسجيل الدخول' })).toBeInTheDocument();
    expect(router.state.location.pathname).toBe('/login');
  });

  it('validates the CPR before calling the API', async () => {
    const login = vi.fn();
    server.use(http.post('*/api/v1/auth/login', () => login()));
    renderApp('/login');

    await fillAndSubmit('abc');

    expect(await screen.findByText('الرقم الشخصي يجب أن يتكون من أرقام فقط (حتى 12 رقمًا).')).toBeInTheDocument();
    expect(login).not.toHaveBeenCalled();
  });

  it('keeps only digits in the CPR field', async () => {
    renderApp('/login');
    const user = userEvent.setup();
    const cpr = await screen.findByLabelText('الرقم الشخصي (CPR)');

    await user.type(cpr, '90a12-3456789012345');

    expect(cpr).toHaveValue('901234567890');
  });

  it('logs in with the CSRF cookie and opens the shell', async () => {
    let xsrfHeader: string | null = null;
    let body: unknown;
    server.use(
      http.post('*/api/v1/auth/login', async ({ request }) => {
        xsrfHeader = request.headers.get('X-XSRF-TOKEN');
        body = await request.json();
        server.use(signedIn());
        return HttpResponse.json({ data: demoViewer });
      }),
    );
    const { router } = renderApp('/login');

    await fillAndSubmit();

    expect(await screen.findByRole('heading', { level: 1, name: 'الدورة الحالية' })).toBeInTheDocument();
    expect(router.state.location.pathname).toBe('/');
    expect(xsrfHeader).toBe('token=123');
    expect(body).toEqual({ role: 'student', cpr: '901234567', password: 'secret-pass' });
  });

  it('shows the generic failure with the attempts left', async () => {
    server.use(
      http.post('*/api/v1/auth/login', () =>
        HttpResponse.json(
          { message: 'x', errors: { cpr: ['x'] }, attempts_left: 7 },
          { status: 422 },
        ),
      ),
    );
    renderApp('/login');

    await fillAndSubmit();

    const alert = await screen.findByRole('alert');
    expect(alert).toHaveTextContent('الرقم الشخصي أو كلمة المرور غير صحيحة.');
    expect(alert).toHaveTextContent('تبقّى لك 7 محاولات قبل الإيقاف المؤقت.');
  });

  it('locks the form with a countdown on 429', async () => {
    server.use(
      http.post('*/api/v1/auth/login', () =>
        HttpResponse.json({ message: 'x', retry_after: 900 }, { status: 429, headers: { 'Retry-After': '900' } }),
      ),
    );
    renderApp('/login');

    await fillAndSubmit();

    expect(await screen.findByText('تم إيقاف المحاولة مؤقتًا لحماية حسابك')).toBeInTheDocument();
    expect(screen.getByText('15:00')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'متوقف مؤقتًا' })).toBeDisabled();
    expect(screen.getByLabelText('الرقم الشخصي (CPR)')).toBeDisabled();
  });

  it('retries once when the CSRF token is stale (419)', async () => {
    let calls = 0;
    server.use(
      http.post('*/api/v1/auth/login', () => {
        calls += 1;
        if (calls === 1) return HttpResponse.json({ message: 'x' }, { status: 419 });
        server.use(signedIn());
        return HttpResponse.json({ data: demoViewer });
      }),
    );
    const { router } = renderApp('/login');

    await fillAndSubmit();

    await waitFor(() => expect(router.state.location.pathname).toBe('/'));
    expect(calls).toBe(2);
  });

  it('reports a network failure', async () => {
    server.use(http.post('*/api/v1/auth/login', () => HttpResponse.error()));
    renderApp('/login');

    await fillAndSubmit();

    expect(await screen.findByRole('alert')).toHaveTextContent('تعذّر الاتصال بالخادم');
  });

  it('sends an expired session back to login with a notice', async () => {
    server.use(signedIn());
    const { store, router } = renderApp('/');
    expect(await screen.findByRole('heading', { level: 1, name: 'الدورة الحالية' })).toBeInTheDocument();

    server.use(http.get('*/api/v1/me', () => HttpResponse.json({ message: 'x' }, { status: 401 })));
    await store.dispatch(viewerApi.endpoints.me.initiate(undefined, { forceRefetch: true }));

    expect(await screen.findByText('انتهت جلستك. يرجى تسجيل الدخول مجددًا.')).toBeInTheDocument();
    expect(router.state.location.search).toBe('?expired=1');
  });

  it('refreshes a stale CSRF token and retries instead of signing out', async () => {
    server.use(signedIn());
    const { store, router } = renderApp('/');
    expect(await screen.findByRole('heading', { level: 1, name: 'الدورة الحالية' })).toBeInTheDocument();

    // The server session ended; the remember cookie signs the user in again, but the old CSRF token is gone.
    let csrfRefreshed = false;
    let calls = 0;
    server.use(
      http.get('*/sanctum/csrf-cookie', () => {
        csrfRefreshed = true;
        return new HttpResponse(null, { status: 204 });
      }),
      http.get('*/api/v1/me', () => {
        calls++;
        return calls === 1 ? HttpResponse.json({ message: 'CSRF token mismatch.' }, { status: 419 }) : HttpResponse.json({ data: demoViewer });
      }),
    );
    await store.dispatch(viewerApi.endpoints.me.initiate(undefined, { forceRefetch: true }));

    expect(csrfRefreshed).toBe(true);
    expect(calls).toBe(2);
    expect(screen.queryByText('انتهت جلستك. يرجى تسجيل الدخول مجددًا.')).not.toBeInTheDocument();
    expect(router.state.location.pathname).toBe('/');
  });

  it('logs out from the profile screen and clears cached data', async () => {
    const logout = vi.fn(() => {
      server.use(http.get('*/api/v1/me', () => HttpResponse.json({ message: 'x' }, { status: 401 })));
      return new HttpResponse(null, { status: 204 });
    });
    server.use(signedIn(), http.post('*/api/v1/auth/logout', logout));
    const { store, router } = renderApp('/profile');
    const user = userEvent.setup();

    // Two logout buttons exist in the DOM (sidebar on lg, profile card); use the profile one.
    const buttons = await screen.findAllByRole('button', { name: 'تسجيل الخروج' });
    await user.click(buttons[buttons.length - 1]);

    await waitFor(() => expect(router.state.location.pathname).toBe('/login'));
    expect(logout).toHaveBeenCalledOnce();
    expect(store.getState().session.status).toBe('unknown');
    expect(screen.queryByText('حسين')).not.toBeInTheDocument();
  });

  it('shows the forgot-password guidance without any reset flow', async () => {
    renderApp('/login');
    const user = userEvent.setup();

    await user.click(await screen.findByRole('button', { name: 'نسيت كلمة المرور؟' }));

    expect(screen.getByRole('heading', { name: 'استعادة كلمة المرور' })).toBeVisible();
    expect(screen.queryByRole('textbox', { name: /البريد/ })).not.toBeInTheDocument();
  });
  it('shows the portal and centre names from the admin settings', async () => {
    server.use(
      http.get('*/api/v1/theme', () =>
        HttpResponse.json({ data: { theme: 'ghadeer', name: 'بوابة النور', short_name: 'النور', centre: 'مركز النور', description: '' } }),
      ),
    );
    renderApp('/login');

    expect(await screen.findByText('© مركز النور')).toBeInTheDocument();
    await waitFor(() => expect(document.title).toBe('تسجيل الدخول · بوابة النور'));
  });
  it('shows the admin banner while an admin views the portal as a student', async () => {
    server.use(http.get('*/api/v1/me', () => HttpResponse.json({ data: { ...demoViewer, impersonated_by: { name: 'المسؤول' } } })));
    renderApp('/');

    expect(await screen.findByText(`تتصفّح بوابة ${demoViewer.name} بصفتك المسؤول المسؤول`)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'إنهاء' })).toBeInTheDocument();
  });
});
