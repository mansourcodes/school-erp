import { createSlice } from '@reduxjs/toolkit';

/**
 * Whether this tab has a signed-in session. "expired" is set when a request
 * that used to succeed starts returning 401/419, so the login screen can say why.
 */
export type SessionStatus = 'unknown' | 'authenticated' | 'expired';

const sessionSlice = createSlice({
  name: 'session',
  initialState: { status: 'unknown' as SessionStatus },
  reducers: {
    sessionAuthenticated: (state) => {
      state.status = 'authenticated';
    },
    sessionExpired: (state) => {
      state.status = 'expired';
    },
    sessionEnded: (state) => {
      state.status = 'unknown';
    },
  },
});

export const { sessionAuthenticated, sessionExpired, sessionEnded } = sessionSlice.actions;
export const sessionReducer = sessionSlice.reducer;
export const selectSessionStatus = (state: RootState) => state.session.status;
