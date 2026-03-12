import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Notice, Button } from '@wordpress/components';
import type { TabComponentProps, LogEntry } from '../types';

const LogsTab: React.FC<TabComponentProps> = ({ settings, onUpdate }) => {
    const [logs, setLogs] = useState<LogEntry[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadLogs();
    }, []);

    const loadLogs = async () => {
        try {
            const response = await fetch('/wp-json/wptpn/v1/logs', {
                headers: {
                    'X-WP-Nonce': window.wptpnAdmin?.nonce || '',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setLogs(data.logs || []);
            }
        } catch (error) {
            console.error('Failed to load logs:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="wptpn-logs-tab">
                <Card>
                    <CardBody>
                        <p>{__('Loading logs...', 'wp-telegram-post-notifier')}</p>
                    </CardBody>
                </Card>
            </div>
        );
    }

    return (
        <div className="wptpn-logs-tab">
            <Card>
                <CardHeader>
                    <h2>{__('Notification Logs', 'wp-telegram-post-notifier')}</h2>
                </CardHeader>
                <CardBody>
                    {logs.length === 0 ? (
                        <Notice status="info">
                            {__('No logs found. Logs will appear here when notifications are sent.', 'wp-telegram-post-notifier')}
                        </Notice>
                    ) : (
                        <div className="wptpn-logs-content">
                            <table className="wptpn-logs-table">
                                <thead>
                                    <tr>
                                        <th>{__('ID', 'wp-telegram-post-notifier')}</th>
                                        <th>{__('Post', 'wp-telegram-post-notifier')}</th>
                                        <th>{__('Event', 'wp-telegram-post-notifier')}</th>
                                        <th>{__('Destination', 'wp-telegram-post-notifier')}</th>
                                        <th>{__('Status', 'wp-telegram-post-notifier')}</th>
                                        <th>{__('Date', 'wp-telegram-post-notifier')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {logs.map((log) => (
                                        <tr key={log.id}>
                                            <td>{log.id}</td>
                                            <td>{log.post_id}</td>
                                            <td>{log.event}</td>
                                            <td>{log.destination}</td>
                                            <td>
                                                <span className={`status-${log.status}`}>
                                                    {log.status}
                                                </span>
                                            </td>
                                            <td>{log.created_at}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </CardBody>
            </Card>
        </div>
    );
};

export default LogsTab;
