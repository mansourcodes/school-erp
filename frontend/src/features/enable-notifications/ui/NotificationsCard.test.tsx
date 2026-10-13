import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { renderApp } from '../../../../test/render-app';
import { demoViewer, server } from '../../../../test/server';

const profile = {
  name: demoViewer.name, cpr: '040512345', dob: null, age: null, degree: null, mobile: null, mobile2: null,
  email: null, address: null, student_id: demoViewer.student_id, registration_at: null, iqraa_id_account: null, photo_url: null,
};

describe('NotificationsCard', () => {
  beforeEach(() => {
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })),
      http.get('*/api/v1/student/profile', () => HttpResponse.json({ data: profile })),
      http.get('*/api/v1/push/config', () => HttpResponse.json({ data: { enabled: true, public_key: 'BKey' } })),
    );
  });

  it('saves each type server-side', async () => {
    let patched: unknown;
    server.use(
      http.patch('*/api/v1/student/notification-settings', async ({ request }) => {
        patched = await request.json();
        return HttpResponse.json({ data: { attendance: true, messages: false } });
      }),
    );
    renderApp('/profile');
    const user = userEvent.setup();

    const messages = await screen.findByRole('checkbox', { name: 'رسائل المدرّسين' });
    expect(messages).toBeChecked();
    await user.click(messages);

    await waitFor(() => expect(patched).toEqual({ messages: false }));
    expect(screen.getByRole('checkbox', { name: 'رسائل المدرّسين' })).not.toBeChecked();
    expect(screen.getByRole('checkbox', { name: 'رصد الحضور' })).toBeChecked();
  });

  it('asks iPhone users to install the app first', async () => {
    const ua = vi.spyOn(navigator, 'userAgent', 'get').mockReturnValue('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)');
    renderApp('/profile');

    expect(await screen.findByText(/أضف البوابة إلى الشاشة الرئيسية أولًا/)).toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'تفعيل' })).not.toBeInTheDocument();
    ua.mockRestore();
  });

  it('is hidden when push is not configured on the server', async () => {
    server.use(http.get('*/api/v1/push/config', () => HttpResponse.json({ data: { enabled: false, public_key: null } })));
    renderApp('/profile');

    await screen.findByRole('heading', { level: 2, name: demoViewer.name });
    expect(screen.queryByRole('heading', { name: 'الإشعارات' })).not.toBeInTheDocument();
  });
});
