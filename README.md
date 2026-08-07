<h1 align="center">LoL Client</h1>

<p align="center">A custom <strong>League of Legends</strong> client desktop app built with <a href="https://laravel.com">Laravel 13</a> and <a href="https://nativephp.com">NativePHP 2</a> (Electron).</p>

## About

This project is a desktop application acting as a custom League of Legends client. It is built on top of a standard Laravel 13 skeleton with NativePHP wired in, so the app runs as a native desktop shell (Electron) while the backend stays a regular Laravel app.

- **Backend**: Laravel 13 (PHP ^8.3), SQLite at `database/database.sqlite`
- **Desktop shell**: NativePHP 2 — the window is opened in `app/Providers/NativeAppServiceProvider.php`, and native APIs are under the `Native\` namespace
- **Frontend**: Blade + Vite + Tailwind CSS v4 (CSS-first, no `tailwind.config.js`)

## Requirements

- PHP ^8.3
- Composer
- Node.js 20+ and npm

## Setup

```bash
# 1. Clone the repo, then create the SQLite database file (it is gitignored and
#    not auto-created by Laravel 13's SQLite connector):
touch database/database.sqlite

# 2. Bootstrap the app (composer install, .env, key, migrate, npm install, build):
composer setup
```

`composer setup` copies `.env.example` to `.env`, generates an app key, runs migrations, and builds frontend assets. On a fresh clone, `touch database/database.sqlite` must come first or `migrate` will throw `SQLiteDatabaseDoesNotExistException`.

## Development

Run the desktop app (NativePHP dev server + Vite):

```bash
composer native:dev
```

Run the plain web app (web server + queue worker + logs + Vite):

```bash
composer dev
```

Useful commands:

```bash
composer test          # PHPUnit (in-memory SQLite)
vendor/bin/pint        # Code style (Laravel preset)
npm run build          # Build frontend assets
```

## Known Issues

- **nativephp/desktop 2.2.1** registers `native:migrate:fresh` twice (both `#[AsCommand]` and `protected $name` in `vendor/nativephp/desktop/src/Commands/FreshCommand.php`), which makes most artisan commands fail with `The "native:migrate:fresh" command cannot be found because it is registered under multiple names`. Working commands: `php artisan test`, `route:list`, `migrate:status`, `config:clear`, `native:run`. Fix: remove the `#[AsCommand(...)]` block (and its `use` statement) from that vendor file.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
