import { createSlice, type PayloadAction } from '@reduxjs/toolkit';

/** Set when the API answers with the portal switch (PLAN.md §4): 503 maintenance / 403 closed. */
export type PortalUnavailable = 'maintenance' | 'closed';

const portalSlice = createSlice({
  name: 'portal',
  initialState: { unavailable: null as PortalUnavailable | null },
  reducers: {
    portalUnavailable: (state, action: PayloadAction<PortalUnavailable>) => {
      state.unavailable = action.payload;
    },
    portalAvailable: (state) => {
      state.unavailable = null;
    },
  },
});

export const { portalUnavailable, portalAvailable } = portalSlice.actions;
export const portalReducer = portalSlice.reducer;
export const selectPortalUnavailable = (state: RootState) => state.portal.unavailable;
