import { useDispatch, useSelector } from 'react-redux';

// RootState / AppDispatch are declared globally in app/store, so lower layers
// get typed hooks without importing from the app layer (PLAN.md §4).
export const useAppSelector = useSelector.withTypes<RootState>();
export const useAppDispatch = useDispatch.withTypes<AppDispatch>();
