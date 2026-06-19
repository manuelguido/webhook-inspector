export interface WebhookBin {
    token: string;
    webhook_url: string;
    requests_url: string;
    clear_url: string;
    created_at: string | null;
    last_captured_at: string | null;
}

export interface CapturedWebhookRequest {
    id: number;
    method: string;
    path: string;
    full_url: string;
    headers: Record<string, string[]>;
    query_params: Record<string, unknown>;
    raw_body: string | null;
    json_body: unknown | null;
    has_json_body: boolean;
    content_type: string | null;
    ip_address: string | null;
    user_agent: string | null;
    body_size: number;
    body_truncated: boolean;
    captured_at: string;
}

export interface InspectorLimits {
    max_body_bytes: number;
    max_requests_per_bin: number;
    poll_interval_ms: number;
}

export interface InspectorUrls {
    bin: string;
}
