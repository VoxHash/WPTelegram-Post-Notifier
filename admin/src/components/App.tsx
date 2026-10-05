import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { TabPanel } from '@wordpress/components';
import ConnectionTab from './ConnectionTab';
import DestinationsTab from './DestinationsTab';
import RoutingTab from './RoutingTab';
import TemplateTab from './TemplateTab';
import DeliveryTab from './DeliveryTab';
import AdvancedTab from './AdvancedTab';
import LogsTab from './LogsTab';
import ErrorBoundary from './ErrorBoundary';
import type { Settings } from '../types';

interface AppProps {}

const App: React.FC<AppProps> = () => {
    const [settings, setSettings] = useState<Settings>({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [selectedTab, setSelectedTab] = useState('connection');

    useEffect(() => {
        loadSettings();
    }, []);

    const loadSettings = async () => {
        try {
            const response = await fetch('/wp-json/wptpn/v1/settings', {
                headers: {
                    'X-WP-Nonce': window.wptpnAdmin?.nonce || '',
                },
            });
            
            if (!response.ok) {
                throw new Error('Failed to load settings');
            }
            
            const data = await response.json();
            setSettings(data);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
        } finally {
            setLoading(false);
        }
    };

    const updateSettings = async (newSettings: Settings) => {
        try {
            const response = await fetch('/wp-json/wptpn/v1/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.wptpnAdmin?.nonce || '',
                },
                body: JSON.stringify(newSettings),
            });
            
            if (!response.ok) {
                throw new Error('Failed to update settings');
            }
            
            const data = await response.json();
            if (data.success) {
                setSettings(newSettings);
            } else {
                throw new Error(data.message || 'Update failed');
            }
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Unknown error');
        }
    };

    if (loading) {
        return (
            <div className="wptpn-loading" role="status" aria-live="polite">
                <p>{__('Loading...', 'wp-telegram-post-notifier')}</p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="wptpn-error" role="alert" aria-live="assertive">
                <p>{__('Error:', 'wp-telegram-post-notifier')} {error}</p>
            </div>
        );
    }

    const tabs = [
        {
            name: 'connection',
            title: __('Connection', 'wp-telegram-post-notifier'),
            className: 'tab-connection',
        },
        {
            name: 'destinations',
            title: __('Destinations', 'wp-telegram-post-notifier'),
            className: 'tab-destinations',
        },
        {
            name: 'routing',
            title: __('Routing Rules', 'wp-telegram-post-notifier'),
            className: 'tab-routing',
        },
        {
            name: 'template',
            title: __('Template', 'wp-telegram-post-notifier'),
            className: 'tab-template',
        },
        {
            name: 'delivery',
            title: __('Delivery Options', 'wp-telegram-post-notifier'),
            className: 'tab-delivery',
        },
        {
            name: 'advanced',
            title: __('Advanced', 'wp-telegram-post-notifier'),
            className: 'tab-advanced',
        },
        {
            name: 'logs',
            title: __('Logs', 'wp-telegram-post-notifier'),
            className: 'tab-logs',
        },
    ];

    return (
        <ErrorBoundary>
            <div className="wptpn-admin" role="main" aria-label={__('WP Telegram Post Notifier Settings', 'wp-telegram-post-notifier')}>
                <TabPanel
                    className="wptpn-tab-panel"
                    activeClass="is-active"
                    onSelect={(tabName: string) => setSelectedTab(tabName)}
                    tabs={tabs}
                >
                    {(tab) => (
                        <div className="wptpn-tab-content" role="tabpanel" aria-labelledby={`tab-${tab.name}`}>
                            {tab.name === 'connection' && (
                                <ConnectionTab
                                    settings={settings}
                                    onUpdate={updateSettings}
                                />
                            )}
                            {tab.name === 'destinations' && (
                                <DestinationsTab
                                    settings={settings}
                                    onUpdate={updateSettings}
                                />
                            )}
                            {tab.name === 'routing' && (
                                <RoutingTab
                                    settings={settings}
                                    onUpdate={updateSettings}
                                />
                            )}
                            {tab.name === 'template' && (
                                <TemplateTab
                                    settings={settings}
                                    onUpdate={updateSettings}
                                />
                            )}
                            {tab.name === 'delivery' && (
                                <DeliveryTab
                                    settings={settings}
                                    onUpdate={updateSettings}
                                />
                            )}
                            {tab.name === 'advanced' && (
                                <AdvancedTab
                                    settings={settings}
                                    onUpdate={updateSettings}
                                />
                            )}
                            {tab.name === 'logs' && (
                                <LogsTab
                                    settings={settings}
                                    onUpdate={updateSettings}
                                />
                            )}
                        </div>
                    )}
                </TabPanel>
            </div>
        </ErrorBoundary>
    );
};

export default App;
