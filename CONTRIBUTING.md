# Contributing

Thank you for contributing to Statamic Figma Assets.

## Requirements

- PHP 8.3 or later
- Composer
- Node.js 22
- npm

## Setup

Install PHP and JavaScript dependencies from the repository root:

```bash
composer install
npm ci
```

## Control Panel development

The Control Panel is a Statamic v6 Inertia page. Start the Vite development server while working on files in `resources/js` or `resources/css`:

```bash
npm run cp:dev
```

Build the production bundle with:

```bash
npm run cp:build
```

The generated files in `public/build` are release artifacts and are committed. Include their updated manifest and hashed assets whenever a Control Panel change changes the build output.

## Tests

Run PHP integration tests:

```bash
./vendor/bin/phpunit
```

These tests boot Statamic through Testbench, write to an isolated asset container, and fake all Figma HTTP requests.

Run the browser smoke test:

```bash
npm run cp:build
composer run test-browser-prepare
npm run test:browser
```

It serves the disposable Workbench Statamic application and opens the real Control Panel login boundary in Chromium. Playwright traces, screenshots, and videos are retained in `test-results` when a test fails.

Run every release check before opening a release pull request:

```bash
composer run release:check
```

This runs the PHP suite, installs Node dependencies, verifies the CP build does not change committed artifacts, prepares Workbench, and runs Playwright.

## Configuration changes

Configuration must work with Laravel's `php artisan config:cache` command. Do not place closures, resources, or other non-serializable values in `config/statamic-figma-assets.php`.

For custom behavior, configure class names that implement one of the package contracts:

- `AssetsTransformer` transforms the normalized Figma asset list.
- `BeforeUploadProcessor` processes a temporary downloaded file and returns an existing file path.

The package resolves these classes through Laravel's container, so handlers may use dependency injection. Add a focused integration test whenever changing configuration normalization, handler invocation, or the Figma HTTP boundary.

## Pull requests

Keep changes focused. Include tests for changed import behavior and update documentation when configuration or Control Panel behavior changes. Do not include Figma credentials, live API fixtures, or downloaded customer assets.

## Releases

1. Run `composer run release:check`.
2. Confirm `git status` is clean after the CP build.
3. Verify a Git archive contains `public/build/manifest.json` and its referenced assets.
4. Install the release candidate in a fresh Statamic v6 project, run `php artisan config:cache`, and confirm `php artisan vendor:publish --tag=statamic-figma-assets --force` publishes the bundle.
5. Create and push the version tag after those checks pass.
