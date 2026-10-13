import fsd from '@feature-sliced/steiger-plugin';
import { defineConfig } from 'steiger';

export default defineConfig([
  ...fsd.configs.recommended,
  {
    // Slices follow PLAN.md §4; most have a single consumer until later milestones add theirs.
    rules: { 'fsd/insignificant-slice': 'off' },
  },
  {
    // The app layer keeps the plan's segment names (providers, store, routes, styles).
    files: ['./src/app/**'],
    rules: { 'fsd/segments-by-purpose': 'off' },
  },
  {
    // Tests may reach into a slice to exercise it directly.
    ignores: ['**/*.test.ts', '**/*.test.tsx'],
  },
]);
