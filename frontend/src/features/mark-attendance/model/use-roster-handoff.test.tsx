import { renderHook, waitFor } from '@testing-library/react';
import { http, HttpResponse } from 'msw';
import type { ReactNode } from 'react';
import { Provider } from 'react-redux';
import { setupStore } from '@/app/store';
import { server } from '../../../../test/server';
import { markQueued, useQueued } from '../lib/offline-queue';
import { useRosterHandoff } from './use-roster-handoff';

const CODE = '2026-09-25#16:00#912#11';

function fakeServiceWorker() {
  const container = Object.assign(new EventTarget(), { controller: { postMessage: vi.fn() } });
  Object.defineProperty(navigator, 'serviceWorker', { value: container, configurable: true });
  return container;
}

function renderHandoff(onSaved = vi.fn()) {
  const store = setupStore();
  const wrapper = ({ children }: { children: ReactNode }) => <Provider store={store}>{children}</Provider>;
  renderHook(() => useRosterHandoff(true, onSaved), { wrapper });
  return onSaved;
}

const offer = (container: EventTarget) =>
  container.dispatchEvent(new MessageEvent('message', { data: { type: 'ROSTER_REPLAY', code: CODE, states: { 1: 'attend' } } }));

const sentTypes = (container: ReturnType<typeof fakeServiceWorker>) =>
  container.controller.postMessage.mock.calls.map(([message]) => (message as { type: string }).type);

describe('useRosterHandoff', () => {
  beforeEach(() => localStorage.clear());
  afterEach(() => {
    Reflect.deleteProperty(navigator, 'serviceWorker');
  });

  it('asks the worker for waiting saves when the teacher shell opens', () => {
    const container = fakeServiceWorker();
    renderHandoff();

    expect(container.controller.postMessage).toHaveBeenCalledWith({ type: 'ROSTER_FLUSH' });
  });

  it('resends a handed-over save, then tells the worker it is done', async () => {
    const container = fakeServiceWorker();
    let body: unknown;
    server.use(
      http.put('*/api/v1/teacher/sessions/:code', async ({ request }) => {
        body = await request.json();
        return HttpResponse.json({ data: { updated_at: '2026-09-25T16:05:00Z', created: true, push_pending: 0 } });
      }),
    );
    markQueued(CODE);
    const onSaved = renderHandoff();

    offer(container);

    await waitFor(() => expect(container.controller.postMessage).toHaveBeenCalledWith({ type: 'ROSTER_REPLAY_DONE', code: CODE }));
    expect(body).toEqual({ states: { 1: 'attend' } });
    expect(onSaved).toHaveBeenCalled();
    expect(renderHook(() => useQueued(CODE)).result.current).toBe(false);
  });

  it('keeps the save queued when the resend fails on the network', async () => {
    const container = fakeServiceWorker();
    let attempts = 0;
    server.use(
      http.put('*/api/v1/teacher/sessions/:code', () => {
        attempts += 1;
        return HttpResponse.error();
      }),
    );
    markQueued(CODE);
    const onSaved = renderHandoff();

    offer(container);

    await waitFor(() => expect(attempts).toBe(1));
    await new Promise((resolve) => setTimeout(resolve, 20));
    expect(sentTypes(container)).not.toContain('ROSTER_REPLAY_DONE');
    expect(onSaved).not.toHaveBeenCalled();
    expect(renderHook(() => useQueued(CODE)).result.current).toBe(true);
  });

  it('lets a refused save go, since it would be refused again', async () => {
    const container = fakeServiceWorker();
    server.use(http.put('*/api/v1/teacher/sessions/:code', () => HttpResponse.json({ message: 'changed' }, { status: 422 })));
    markQueued(CODE);
    renderHandoff();

    offer(container);

    await waitFor(() => expect(container.controller.postMessage).toHaveBeenCalledWith({ type: 'ROSTER_REPLAY_DONE', code: CODE }));
    expect(renderHook(() => useQueued(CODE)).result.current).toBe(false);
  });
});
