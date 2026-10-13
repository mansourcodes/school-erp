import { render } from '@testing-library/react';
import { Provider } from 'react-redux';
import { createMemoryRouter, RouterProvider } from 'react-router';
import { appRoutes } from '@/app/routes';
import { setupStore } from '@/app/store';

/** Renders the real route tree and store at `path` (no basename in tests). */
export function renderApp(path = '/') {
  const store = setupStore();
  const router = createMemoryRouter(appRoutes, { initialEntries: [path] });
  const view = render(
    <Provider store={store}>
      <RouterProvider router={router} />
    </Provider>,
  );
  return { ...view, store, router };
}
