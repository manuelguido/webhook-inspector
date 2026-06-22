import type { CapturedWebhookRequest } from '@/types/webhooks';

export const formatBytes = (bytes: number): string => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

export const formatClockTime = (value: string): string =>
    new Intl.DateTimeFormat(undefined, {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(new Date(value));

export const formatDateTime = (value: string): string =>
    new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'medium',
    }).format(new Date(value));

export const requestDisplayLabel = (
    request: CapturedWebhookRequest,
): string => {
    if (
        request.json_body &&
        typeof request.json_body === 'object' &&
        !Array.isArray(request.json_body) &&
        'event' in request.json_body &&
        typeof request.json_body.event === 'string'
    ) {
        return request.json_body.event;
    }

    return request.content_type || request.path;
};

export const requestParseState = (
    request: CapturedWebhookRequest,
): 'empty' | 'json' | 'raw' | 'truncated' => {
    if (request.body_truncated) {
        return 'truncated';
    }

    if (request.has_json_body) {
        return 'json';
    }

    if (request.raw_body) {
        return 'raw';
    }

    return 'empty';
};

export const requestParseTone = (
    request: CapturedWebhookRequest,
): 'muted' | 'success' | 'warning' => {
    if (request.body_truncated) {
        return 'warning';
    }

    if (request.has_json_body) {
        return 'success';
    }

    return 'muted';
};

export const buildCurlCommand = (
    webhookUrl: string,
): string => `curl -X POST "${webhookUrl}?source=portfolio" \\
  -H "Content-Type: application/json" \\
  -H "X-Demo-Signature: whsec_demo_123" \\
  -d '{
    "event": "invoice.paid",
    "id": "evt_demo_001",
    "created_at": "2026-06-17T18:42:00Z",
    "data": {
      "invoice_id": "inv_2026_0042",
      "customer": "ada@example.com",
      "amount": 129.90,
      "currency": "USD"
    }
  }'`;
