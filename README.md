# a-pb/horizon-ui

Enhanced UI for Laravel Horizon — adds job name search/filter to **Pending**, **Completed**, and **Failed** job tabs.

## Installation

```bash
composer require a-pb/horizon-ui
```

The package auto-discovers its service provider. No additional configuration needed.

## What it does

This package extends the official `laravel/horizon` dashboard with job name filtering:

- **Pending Jobs** — search input to filter by job class name
- **Completed Jobs** — search input to filter by job class name
- **Failed Jobs** — search input to filter by job class name (alongside the existing tag search)

The search is case-insensitive and matches partial phrases. Results are debounced (500ms) for a smooth experience.

## How it works

1. **Controller overrides** — Registers enhanced versions of `PendingJobsController`, `CompletedJobsController`, and `FailedJobsController` that accept a `query` parameter and scan Redis for matching job names. When no `query` parameter is present, the original Horizon logic is used via `parent::index()`.
2. **Middleware JS swap** — A `SwapHorizonAssets` middleware (appended to the `horizon` middleware group) replaces the inlined `<script>` block in Horizon's HTML response with our enhanced JS bundle that includes search UI components. No Blade views are overridden — Horizon's `layout.blade.php` is used as-is.
3. **Zero config** — Routes are registered with the same names as Horizon's originals, so Laravel's last-registered-wins behavior ensures our enhanced controllers handle the requests.

## What is NOT overridden

- Horizon's Blade views (layout, etc.)
- Horizon's CSS styles
- All other Horizon API routes (dashboard, metrics, batches, monitoring, etc.)
- Horizon's authentication and authorization logic

## Requirements

- PHP ^8.2
- Laravel ^11.0 | ^12.0
- Laravel Horizon ^5.0

## Development — rebuilding the frontend

The `dist/app.js` bundle is pre-built and committed to the repository. To rebuild it after modifying files in `patches/`:

```bash
# Provide the path to the installed laravel/horizon package
HORIZON_PATH=/path/to/vendor/laravel/horizon ./build.sh
```

The build script:
1. Copies all JS sources from Horizon's vendor directory
2. Overlays modified Vue components from `patches/`
3. Runs Vite to produce `dist/app.js`
4. Cleans up the temporary source files

## License

MIT
