/**
 * Shared TypeScript type definitions for WP Telegram Post Notifier
 */

export interface Destination {
    id: string;
    name: string;
    chat_id: string;
    template_id?: string;
    enabled: boolean;
}

export interface LogEntry {
    id: number;
    post_id: number;
    event: string;
    destination: string;
    status: 'success' | 'failed' | 'pending';
    created_at: string;
    message?: string;
}

export interface Settings {
    bot_token?: string;
    destinations?: Destination[];
    template?: string;
    parse_mode?: 'MarkdownV2' | 'HTML' | 'None';
    enable_logging?: boolean;
    enable_updates?: boolean;
    enable_scheduled?: boolean;
    rate_limit_delay?: number;
    [key: string]: unknown;
}

export interface TabComponentProps {
    settings: Settings;
    onUpdate: (settings: Settings) => void;
}

declare global {
    interface Window {
        wptpnAdmin?: {
            nonce: string;
            apiUrl: string;
        };
    }
}
