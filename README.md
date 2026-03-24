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

1. **Controller overrides** — The package registers enhanced versions of `PendingJobsController`, `CompletedJobsController`, and `FailedJobsController` that accept a `query` parameter and scan Redis batches for matching job names.
2. **View override** — The package overrides the `horizon::layout` Blade view to load its own compiled `app.js` which includes the search UI components.
3. **Zero config** — Routes are registered with the same names as Horizon's originals, so Laravel's last-registered-wins behavior ensures our enhanced controllers handle the requests.

## Requirements

- PHP ^8.2
- Laravel ^11.0 | ^12.0
- Laravel Horizon ^5.0

## Building frontend (for development)

```bash
npm install
npm run build
```

## License

MIT
