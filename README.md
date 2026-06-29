# Webhook Inspector

Webhook Inspector is a request-bin style debugging tool for integrations. It gives you a no-login endpoint where you can send HTTP requests and immediately inspect what actually arrived.

The project is designed for local demos, portfolio review, and webhook workflow testing. It is useful when a provider says it sent an event and you need to inspect the method, headers, query parameters, content type, parsed body, raw fallback, timestamps, IP address, and user agent in one place.

## Integration Debugging Loop

Webhook development usually needs a disposable URL and a clear answer to "what did the sender actually send?"

Webhook Inspector provides that loop:

1. Open the app.
2. Copy the generated webhook URL.
3. Send a request from `curl`, a webhook provider, or another local app.
4. Watch the request appear in the inspector.
5. Inspect headers, query params, body, and metadata.

No account is required. A browser session gets its own demo bin automatically.

## Feature Set

- Automatic demo bin creation per browser session.
- Long random capture tokens with a minimum length of 32 characters.
- Capture endpoint for `GET`, `POST`, `PUT`, `PATCH`, and `DELETE`.
- Request list with live polling and manual refresh.
- Request detail view for overview, headers, query parameters, parsed body, and raw payload.
- JSON parsing when the body is valid JSON.
- Raw text storage for non-JSON payloads.
- Body-size tracking and truncation flag.
- Copyable webhook URL.
- Copyable `curl` example.
- Clear all captured requests for the current bin.
- Request retention limit per bin.
- Rate limiting on capture routes.
- Database-backed persistence for bins and captured requests.

## Request Lifecycle

The capture endpoint is:

```text
/hook/{token}
```

The route is handled by `CaptureWebhookController`, which resolves the `WebhookBin` by token. If the token is missing, the response is a JSON 404:

```json
{
  "ok": false,
  "captured": false,
  "message": "Webhook bin not found."
}
```

Valid requests are passed to `CaptureWebhookRequest`, which stores:

- HTTP method
- path and full URL
- headers as `array<string, list<string>>`
- query parameters
- raw body, truncated to the configured byte limit
- parsed JSON body when decoding succeeds
- content type
- IP address
- user agent
- original body size
- truncation state
- capture timestamp

After capture, the bin's `last_captured_at` timestamp is updated and old requests are pruned according to `WEBHOOK_MAX_REQUESTS_PER_BIN`.

## Configuration

Webhook-specific settings live in `config/webhooks.php`.

| Environment variable | Default | Meaning |
| --- | ---: | --- |
| `WEBHOOK_TOKEN_LENGTH` | `48` | Random token length for new bins. Minimum enforced value is 32. |
| `WEBHOOK_MAX_BODY_BYTES` | `65536` | Maximum stored body bytes per request. Original size is still recorded. |
| `WEBHOOK_MAX_REQUESTS_PER_BIN` | `50` | Number of latest requests kept per bin. |
| `WEBHOOK_RATE_LIMIT_PER_MINUTE` | `120` | Capture route limit per token and IP address. |
| `WEBHOOK_POLL_INTERVAL_MS` | `4000` | Client polling interval for request refresh. |

## Routes

| Route | Method | Purpose |
| --- | --- | --- |
| `/` | `GET` | Inspector UI and current session bin |
| `/inspector/bin` | `GET` | Current session bin payload |
| `/inspector/{bin:token}/requests` | `GET` | Captured requests for a bin |
| `/inspector/{bin:token}/requests` | `DELETE` | Clear captured requests for a bin |
| `/hook/{token}` | `GET`, `POST`, `PUT`, `PATCH`, `DELETE` | Capture a webhook request |

The capture route uses Laravel's `throttle:webhook-capture` limiter. The limiter key combines the route token and request IP.

## Example Request

Replace the host and token with the URL shown in the application:

```bash
curl -X POST "http://localhost:8000/hook/YOUR_TOKEN?source=docs" \
  -H "Content-Type: application/json" \
  -H "X-Demo-Signature: whsec_demo_123" \
  -d '{"event":"invoice.paid","id":"evt_demo_001","amount":129.90}'
```

## Architecture

```text
app/Models/WebhookBin.php
    Public bin identified by token.

app/Models/CapturedRequest.php
    Stored HTTP request with casts and inspector presentation shape.

app/Actions/Webhooks/CreateWebhookBin.php
    Generates unique random bin tokens.

app/Actions/Webhooks/CaptureWebhookRequest.php
    Normalizes and stores incoming request data.

app/Actions/Webhooks/PruneCapturedRequests.php
    Keeps each bin under the configured retention limit.

app/Http/Controllers/Webhooks/WebhookInspectorController.php
    Inertia page, current bin endpoint, request polling endpoint, clear endpoint.

app/Http/Controllers/Webhooks/CaptureWebhookController.php
    Public capture endpoint.

resources/js/pages/WebhookInspector.vue
    Workbench state, polling, refresh, clearing, selection, and drawers.

resources/js/components/webhooks/
    Header, URL panel, request list, request detail, body viewer, curl example,
    summaries, and sidebar components.
```

## Database Tables

`webhook_bins`

- `id`
- `token`
- `last_captured_at`
- timestamps

`captured_requests`

- `webhook_bin_id`
- request identity and URL fields
- headers and query parameters as JSON
- raw body and parsed JSON body
- content metadata
- body size and truncation flag
- capture timestamp

Captured requests cascade when their bin is deleted.

## Stack

| Layer | Tools |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+, Inertia Laravel |
| Frontend | Vue 3, TypeScript, Vite, Tailwind CSS 4 |
| Storage | Database-backed webhook bins and captured requests |
| Quality | PHPUnit, Pint, Larastan, ESLint, Prettier, vue-tsc |

## Local Setup

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Configure the database connection in `.env` before running migrations.

```bash
php artisan migrate
```

Run the local stack:

```bash
composer dev
```

Or run Laravel and Vite separately:

```bash
php artisan serve
npm run dev
```

## Build

```bash
npm run build
```

## Tests And Quality

Run the full backend test script:

```bash
composer test
```

That script clears config, runs Pint in check mode, runs PHPStan through `composer types:check`, and then runs PHPUnit.

Useful individual checks:

```bash
composer lint:check
composer types:check
npm run lint:check
npm run format:check
npm run types:check
npm run build
```

Apply formatting fixes:

```bash
composer lint
npm run lint:fix
npm run format
```

## Test Coverage

The feature suite covers the core webhook behavior:

- home page creates a demo bin
- current-bin endpoint returns bin metadata
- JSON webhook capture
- raw text capture
- large-body truncation
- per-bin pruning
- clearing captured requests
- invalid token JSON 404 response

## Current Limits

- Polling is used instead of WebSockets or Server-Sent Events.
- Bins are session-associated but not owned by authenticated users.
- There is no request replay feature.
- File uploads are not stored as files; the body is captured as text up to the configured limit.
- This is not a public multi-tenant request-bin service. It is suitable for local/demo use unless additional abuse controls, authentication, and retention policies are added.

## Contributing

Good changes should preserve the capture contract and keep unsafe input handling boring.

Useful areas:

- richer body rendering for form data or XML
- request replay/export
- tests around headers and nested query parameters
- auth-owned bins
- SSE/WebSocket updates as an alternative to polling
- scheduled pruning for old bins

Before opening a pull request, run:

```bash
composer test
npm run lint:check
npm run format:check
npm run types:check
npm run build
```

## License

Webhook Inspector is open-sourced under the MIT license. See `LICENSE`.
