import { combineReducers, configureStore } from '@reduxjs/toolkit';
import { setupListeners } from '@reduxjs/toolkit/query';
import { themeReducer } from '@/features/theme-switch';
import { baseApi, sessionReducer } from '@/shared/api';

// Reducers are registered only here (PLAN.md §4).
const rootReducer = combineReducers({
  [baseApi.reducerPath]: baseApi.reducer,
  session: sessionReducer,
  theme: themeReducer,
});

export function setupStore(preloadedState?: Partial<ReturnType<typeof rootReducer>>) {
  const store = configureStore({
    reducer: rootReducer,
    preloadedState,
    middleware: (getDefault) => getDefault().concat(baseApi.middleware),
  });
  // refetchOnFocus / refetchOnReconnect for queries that opt in.
  setupListeners(store.dispatch);
  return store;
}

export type AppStore = ReturnType<typeof setupStore>;

declare global {
  // Global so shared/lib can type its hooks without importing from the app layer.
  type RootState = ReturnType<typeof rootReducer>;
  type AppDispatch = AppStore['dispatch'];
}
