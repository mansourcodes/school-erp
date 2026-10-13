import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { renderApp } from '../../../test/render-app';
import { demoViewer, server } from '../../../test/server';

describe('portal switch (portal.mode)', () => {
  it('shows the maintenance screen and recovers on retry', async () => {
    let maintenance = true;
    server.use(
      http.get('*/api/v1/me', () =>
        maintenance
          ? HttpResponse.json({ message: 'x', code: 'portal_maintenance' }, { status: 503, headers: { 'Retry-After': '300' } })
          : HttpResponse.json({ data: demoViewer }),
      ),
    );
    renderApp('/');
    const user = userEvent.setup();

    expect(await screen.findByRole('heading', { name: 'البوابة تحت الصيانة' })).toBeInTheDocument();

    maintenance = false;
    await user.click(screen.getByRole('button', { name: 'إعادة المحاولة' }));
    expect(await screen.findByRole('heading', { level: 1, name: 'الدورة الحالية' })).toBeInTheDocument();
  });

  it('shows the closed screen without a login form', async () => {
    server.use(http.get('*/api/v1/me', () => HttpResponse.json({ message: 'x', code: 'portal_closed' }, { status: 403 })));
    renderApp('/login');

    expect(await screen.findByRole('heading', { name: 'البوابة مغلقة حاليًا' })).toBeInTheDocument();
    expect(screen.queryByLabelText('الرقم الشخصي (CPR)')).not.toBeInTheDocument();
  });
});
