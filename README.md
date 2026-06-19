# Webhook Inspector

Public request-bin style demo built with Laravel, Inertia, Vue, TypeScript and Tailwind.

Webhook Inspector gives a reviewer or recruiter a no-login URL where they can send HTTP requests and inspect what arrived: method, headers, query params, content type, raw body, parsed JSON, body size, timestamp and truncation state.

## Features

- Public demo bin created automatically per browser session.
- `/hook/{token}` capture endpoint for `GET`, `POST`, `PUT`, `PATCH` and `DELETE`.
- Request list with polling and manual refresh.
- Request detail tabs for overview, headers, query, body and raw payload.
- Pretty JSON rendering when the incoming body is valid JSON.
- Copyable webhook URL and copyable curl example.
- SQLite persistence by default.
- Basic anti-abuse protections: long random tokens, rate limiting, max body size, request retention limit and no file storage.

## Stack

- Laravel 13
- Inertia 3
- Vue 3
- TypeScript
- Tailwind CSS 4
- SQLite
- PHPUnit, Pint, Larastan, ESLint and vue-tsc

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

This workspace is intended to run locally through Laravel Herd. Open the Herd URL for this project, then use the webhook URL shown on the page.

## Example Curl

```bash
curl -X POST "https://webhook-inspector.test/hook/YOUR_TOKEN?source=docs" \
  -H "Content-Type: application/json" \
  -H "X-Demo-Signature: whsec_demo_123" \
  -d '{"event":"invoice.paid","id":"evt_demo_001","amount":129.90}'
```

Replace the host and token with the URL shown in the app.

## Useful Commands

```bash
php artisan migrate
php artisan test
composer lint:check
composer types:check
npm run lint:check
npm run types:check
npm run build
```

## What It Demonstrates

- HTTP request capture and debugging.
- Webhook-oriented product UX.
- Laravel routing, controllers, actions, models and migrations.
- Safe handling of raw request bodies.
- JSON parsing with graceful fallback to raw text.
- Temporary demo persistence with pruning.
- Polling-based UI state without WebSockets.
