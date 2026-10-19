# DutyTrack PWA

DutyTrack uses Laravel 12, Livewire 4, Flux 2, Tailwind 4 and Vite 7. The PWA uses browser APIs directly, with no added dependencies. Existing routes, roles, authentication, database operations and Livewire forms are unchanged.

## Features and files

- `app/Http/Controllers/PwaController.php`, `routes/web.php`: public manifest and worker endpoints, with request-derived subdirectory scope and correct content/cache headers. Build asset contents and worker code determine the version automatically.
- `resources/js/service-worker.js`: precaches only public build files, branded icons and the offline page. Navigations use the network and fall back only on network failure. Authentication, API, Livewire, uploads and mutations are never stored. Static assets use cache first. Cache cleanup affects only this deployment's DutyTrack caches; the previous build is retained for existing tabs.
- `resources/js/pwa.js`, `resources/js/app.js`: secure-context registration, failure logging, browser-confirmed install prompts, iOS Safari instructions, seven-day install dismissal, offline notice and explicit update/reload confirmation. Other open tabs never reload automatically. No offline writes or replay queue.
- `resources/views/partials/head.blade.php`: manifest, Apple icon, theme/status-bar metadata and safe-area viewport shared by public, authentication and app layouts.
- `public/pwa/`: self-contained branded offline page, 192/512 maskable PNGs, and 180px Apple icon derived from `public/logo.png`, with padding inside the maskable safe zone. The offline page embeds its logo so it works at any failed navigation URL.
- `resources/css/app.css`: safe-area spacing, compact header spacing, touch target improvements, mobile input sizing, modal width constraint and accessible PWA notices. Existing responsive Flux navigation, scrolling tables and dashboard breakpoints are reused.
- `tests/Feature/PwaTest.php`, `tests/js/pwa.test.mjs`: manifest/icon/subdirectory checks and worker cache, private-request, offline and lifecycle checks.

## Deployment

1. Run `npm ci && npm run build` and deploy the PHP code, views, `resources/js/service-worker.js`, `public/pwa/`, and generated `public/build/` together. The worker source is read by PHP at runtime.
2. Serve the public directory over HTTPS. HTTP localhost is supported for development. A plain HTTP `.test` hostname or LAN IP cannot register a worker. Enable HTTPS for your Herd site when testing locally.
3. Keep the existing `APP_URL` accurate, including any deployment subdirectory, and configure the reverse proxy/web server to preserve the application's base path. Manifest and worker scope follow the request base URL. No new environment variables are required.
4. Do not cache `/sw.js` or `/manifest.webmanifest` at a CDN or reverse proxy. Preserve their response headers and allow `/sw.js` through Laravel's front controller. Serve `public/pwa/` and `public/build/` as public static files. Deploy atomically so worker installation cannot encounter half-published assets. Only public assets belong in `build/assets`.
5. Keep at least the previous build's hashed files available on the server for long-lived tabs and lazy imports. One previous browser cache is retained, but tabs several deployments old should save work and reload.
6. In Vite development, production caches are not representative: test a production build without an active Vite hot file/dev server. Disable or unregister the worker in DevTools during everyday frontend development if needed.

Worker install fails safely if any precached asset is unavailable; the previous worker remains active. Errors are logged in the browser console. The offline fallback needs one successful online visit to install. Live data always requires a connection; a failed submission is never automatically retried. Recheck the server before manually retrying a submission whose result is uncertain.

## Verification

Automated:

```sh
npm run build
php artisan test --compact
node --test tests/js/pwa.test.mjs
vendor/bin/pint --test
```

Browser/device checks (manual):

1. Open the HTTPS production build. In DevTools > Application, inspect Manifest and Service Workers. Check icon URLs, standalone display, expected base scope and an activated worker. The start URL `/dashboard` should follow the existing authentication/role redirects.
2. Android Chrome: use the in-app Install DutyTrack button when offered, or the browser's Install app menu. Dismissal suppresses the custom prompt for seven days. Launch from the home screen and confirm the existing login, role navigation and logout flow.
3. Desktop Chrome/Edge: use the install button or address-bar install action. Confirm the standalone window and navigation. Safari installation availability depends on OS/browser support.
4. iPhone/iPad Safari: follow Share → Add to Home Screen → Add. Launch the home-screen icon and check the icon, theme and safe areas. No programmatic install button is shown on iOS.
5. After the worker is active, enable DevTools Offline and perform a full navigation/reload to a dashboard URL. Expect the branded offline fallback, with no private content. Retry after reconnecting. A currently open page may still show data already in memory; this is not persistent offline storage. Livewire navigation cannot fetch fresh content offline; the connectivity notice explains this, and a full reload opens the fallback.
6. Inspect Cache Storage after visiting private routes and logging out: it must contain only `/pwa/` and `/build/assets/` resources. It must never contain dashboards, login/logout, storage uploads, Livewire or API responses. Confirm failed offline form submissions do not replay after reconnecting.
7. Change a frontend asset, build and deploy again, then reload an online tab or trigger Update in DevTools. The new worker should wait and show an update notice. Keep an unsaved form open: nothing should reload until you accept Update and reload and confirm that work is saved. Test two tabs: only the accepting tab reloads. Check that the new worker version activates, current assets load, and unrelated application caches remain untouched.
8. Check all roles at 320px, 375px, tablet and desktop widths, including landscape and 200% zoom. Open/close the sidebar, scroll tables, use filters/dropdowns, submit forms, open modals and check dashboard cards, login and safe-area insets. Test keyboard focus and screen-reader notice announcements. Notices are dismissible except the connectivity status, which clears on reconnect.

Automated worker checks simulate browser events and Cache Storage; they do not prove real browser registration or OS installation. Real Android, iOS, desktop installation and visual/mobile checks remain required before production sign-off. Browser-specific install criteria and user engagement can delay the install event; no button appears until the browser offers it. See [MDN's service worker lifecycle guidance](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API/Using_Service_Workers) and [install prompt API](https://developer.mozilla.org/en-US/docs/Web/API/Window/beforeinstallprompt_event).

## Results in this workspace

- Production Vite build passed.
- Laravel suite: 89 passed, 7 skipped, 450 assertions.
- Node worker tests: 2 passed.
- Changed PHP files pass Pint; repository-wide Pint reports 30 existing style issues in untouched files.
- Live HTTPS HEAD checks on `dutytrack.test` returned 200 for the manifest, worker, both manifest icons and offline page, with the expected content types and worker scope header.
- In-app browser automation was unavailable. OS installation, browser lifecycle behavior and visual mobile checks were not performed. An existing `public/hot` file is present; use a deployment without Vite's hot file when checking the production build.
