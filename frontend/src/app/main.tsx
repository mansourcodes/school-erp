// Self-hosted fonts (PLAN.md §4): Rubik for text, IBM Plex Sans Arabic for titles.
import '@fontsource/rubik/400.css';
import '@fontsource/rubik/500.css';
import '@fontsource/rubik/600.css';
import '@fontsource/rubik/700.css';
import '@fontsource/ibm-plex-sans-arabic/500.css';
import '@fontsource/ibm-plex-sans-arabic/600.css';
import '@fontsource/ibm-plex-sans-arabic/700.css';
import './styles/app.css';

import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { App } from './providers';
import { createAppRouter } from './routes';
import { setupStore } from './store';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App store={setupStore()} router={createAppRouter()} />
  </StrictMode>,
);
