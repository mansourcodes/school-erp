import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import type { Profile } from '@/entities/viewer';
import { renderApp } from '../../../../../test/render-app';
import { demoViewer, server } from '../../../../../test/server';

const profile: Profile = {
  name: 'حسين علي عبدالله الحايكي',
  cpr: '040512345',
  dob: '2004-05-18',
  age: 22,
  degree: 'بكالوريوس',
  mobile: '36001234',
  mobile2: null,
  email: 'hussain.a@example.com',
  address: 'المنامة',
  student_id: '20240158',
  registration_at: '2024-09-01',
  iqraa_id_account: 'IQ-58214',
  photo_url: null,
};

describe('ProfilePage', () => {
  beforeEach(() => {
    server.use(
      http.get('*/api/v1/me', () => HttpResponse.json({ data: demoViewer })),
      http.get('*/api/v1/student/profile', () => HttpResponse.json({ data: profile })),
    );
  });

  it('shows the profile read-only with the CPR masked until revealed', async () => {
    renderApp('/profile');
    const user = userEvent.setup();

    expect(await screen.findByRole('heading', { level: 2, name: profile.name })).toBeInTheDocument();
    expect(screen.getByText('•••••2345')).toBeInTheDocument();
    expect(screen.queryByText('040512345')).not.toBeInTheDocument();

    await user.click(screen.getByRole('button', { name: 'إظهار الرقم الشخصي' }));
    expect(screen.getByText('040512345')).toBeInTheDocument();

    expect(screen.getByText('IQ-58214')).toBeInTheDocument();
    expect(screen.getByText('لتحديث الصورة راجع مدرّسك أو إدارة المركز')).toBeInTheDocument();
    // No editable fields: the profile is display-only.
    expect(screen.queryByRole('textbox')).not.toBeInTheDocument();
  });

  it('keeps logout; the theme is set by the centre, not here', async () => {
    renderApp('/profile');

    await screen.findByRole('heading', { level: 2, name: profile.name });
    expect(screen.getAllByRole('button', { name: 'تسجيل الخروج' }).length).toBeGreaterThan(0);
    expect(screen.queryByRole('group', { name: 'المظهر' })).not.toBeInTheDocument();
  });
});
