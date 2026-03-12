import React, { Component, ErrorInfo, ReactNode } from 'react';
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';

interface Props {
    children: ReactNode;
}

interface State {
    hasError: boolean;
    error: Error | null;
}

class ErrorBoundary extends Component<Props, State> {
    constructor(props: Props) {
        super(props);
        this.state = {
            hasError: false,
            error: null,
        };
    }

    static getDerivedStateFromError(error: Error): State {
        return {
            hasError: true,
            error,
        };
    }

    componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
        console.error('WP Telegram Post Notifier Error:', error, errorInfo);
    }

    render(): ReactNode {
        if (this.state.hasError) {
            return (
                <div className="wptpn-error-boundary" role="alert">
                    <Notice status="error" isDismissible={false}>
                        <strong>{__('An error occurred', 'wp-telegram-post-notifier')}</strong>
                        <p>
                            {__(
                                'Please refresh the page and try again. If the problem persists, check the browser console for details.',
                                'wp-telegram-post-notifier'
                            )}
                        </p>
                        {this.state.error && (
                            <details>
                                <summary>{__('Error details', 'wp-telegram-post-notifier')}</summary>
                                <pre>{this.state.error.toString()}</pre>
                            </details>
                        )}
                    </Notice>
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
