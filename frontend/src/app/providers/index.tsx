import { Provider } from 'react-redux';
import { type createBrowserRouter, RouterProvider } from 'react-router';
import { useThemeSync } from '@/features/theme-switch';
import type { AppStore } from '../store';

function ThemeSync() {
  useThemeSync();
  return null;
}

interface AppProps {
  store: AppStore;
  router: ReturnType<typeof createBrowserRouter>;
}

export function App({ store, router }: AppProps) {
  return (
    <Provider store={store}>
      <ThemeSync />
      <RouterProvider router={router} />
    </Provider>
  );
}
