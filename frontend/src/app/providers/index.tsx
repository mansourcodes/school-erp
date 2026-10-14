import { Provider } from 'react-redux';
import { type createBrowserRouter, RouterProvider } from 'react-router';
import { usePortalTheme } from '@/features/portal-theme';
import { PushRefresh } from '../pwa/PushRefresh';
import { UpdatePrompt } from '../pwa/UpdatePrompt';
import type { AppStore } from '../store';

function PortalTheme() {
  usePortalTheme();
  return null;
}

interface AppProps {
  store: AppStore;
  router: ReturnType<typeof createBrowserRouter>;
}

export function App({ store, router }: AppProps) {
  return (
    <Provider store={store}>
      <PortalTheme />
      <RouterProvider router={router} />
      <UpdatePrompt />
      <PushRefresh />
    </Provider>
  );
}
