import js from '@eslint/js';
import reactHooks from 'eslint-plugin-react-hooks';
import globals from 'globals';
import tseslint from 'typescript-eslint';

// Feature-Sliced Design: a layer may import only from layers below it (PLAN.md §4).
// Steiger checks slice isolation and public APIs; this rule checks the layer direction.
const LAYERS = ['shared', 'entities', 'features', 'widgets', 'pages', 'app'];

const layerBoundaries = LAYERS.slice(0, -1).map((layer, i) => ({
  files: [`src/${layer}/**/*.{ts,tsx}`],
  ignores: ['**/*.test.{ts,tsx}'],
  rules: {
    'no-restricted-imports': [
      'error',
      {
        patterns: LAYERS.slice(i + 1).map((upper) => ({
          group: [`@/${upper}`, `@/${upper}/*`],
          message: `FSD: "${layer}" must not import from the higher layer "${upper}".`,
        })),
      },
    ],
  },
}));

export default tseslint.config(
  { ignores: ['dist', 'node_modules'] },
  {
    files: ['**/*.{ts,tsx}'],
    extends: [js.configs.recommended, ...tseslint.configs.recommended],
    languageOptions: { ecmaVersion: 2022, globals: globals.browser },
    plugins: { 'react-hooks': reactHooks },
    rules: {
      ...reactHooks.configs.recommended.rules,
      '@typescript-eslint/no-explicit-any': 'error',
    },
  },
  ...layerBoundaries,
);
