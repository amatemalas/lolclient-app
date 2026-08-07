# AGENTS.md

## Project

Laravel 13 (PHP ^8.3) + NativePHP 2 (Electron) desktop app — the default Laravel skeleton with NativePHP wired in. The desktop shell opens in `app/Providers/NativeAppServiceProvider.php`; NativePHP APIs are under the `Native\` namespace. App DB is SQLite at `database/database.sqlite`; NativePHP keeps its own `database/nativephp.sqlite`.

## Commands

- `composer setup` — full bootstrap: `composer install`, copy `.env`, `key:generate`, `migrate --force`, `npm install --ignore-scripts`, `npm run build`
- `composer test` — `config:clear` + `php artisan test` (phpunit 12, in-memory SQLite)
- `composer dev` — concurrently runs web server, queue, pail logs, and Vite
- `composer native:dev` — `php artisan native:run` + Vite (desktop dev loop)
- `vendor/bin/pint` — code style (Laravel preset; there is no pint config file)
- `npm run build` / `npm run dev` — Vite, Tailwind v4 via `@tailwindcss/vite`

## Gotchas

- **Fresh clone**: `database/database.sqlite` is gitignored (`database/.gitignore` has `*.sqlite*`). Laravel 13's SQLite connector throws `SQLiteDatabaseDoesNotExistException` instead of creating the file, so `touch database/database.sqlite` before running `composer setup` or `php artisan migrate`.
- **Known bug (nativephp/desktop 2.2.1)**: `vendor/nativephp/desktop/src/Commands/FreshCommand.php` declares both `#[AsCommand]` and legacy `protected $name`, double-registering `native:migrate:fresh`. As a result most artisan commands fail with `The "native:migrate:fresh" command cannot be found because it is registered under multiple names` (e.g. `php artisan list`, `fresh`, `migrate`, `queue:work`, `native:version`, `native:install`). Commands that still work: `php artisan test`, `route:list`, `migrate:status`, `config:clear`, `native:run`. Verified fix: delete the `#[AsCommand(...)]` block (and its `use ...AsCommand;`) in that vendor file. A `composer update` will restore it, and the post-update `php artisan native:install` step then fails.
- **Test output is JSON**, not plain PHPUnit text, because `laravel/pao` is installed (e.g. `{"tool":"phpunit","result":"passed",...}`).
- `.npmrc` sets `ignore-scripts=true`, so `npm install` never runs lifecycle scripts.
- Tailwind v4 is CSS-first: no `tailwind.config.js`; theme/fonts are configured in `resources/css/app.css`.
- `.editorconfig`: 4-space indent for PHP/Blade, 2-space for YAML.
