import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Button, TextControl, Notice, Card, CardBody, CardHeader } from '@wordpress/components';
import type { TabComponentProps } from '../types';

const ConnectionTab: React.FC<TabComponentProps> = ({ settings, onUpdate }) => {
    const [botToken, setBotToken] = useState(settings.bot_token || '');
    const [testing, setTesting] = useState(false);
    const [testResult, setTestResult] = useState<{ success: boolean; message: string } | null>(null);
    const [showToken, setShowToken] = useState(false);

    const handleSave = () => {
        onUpdate({
            ...settings,
            bot_token: botToken,
        });
    };

    const handleTestConnection = async () => {
        setTesting(true);
        setTestResult(null);

        try {
            const response = await fetch('/wp-json/wptpn/v1/test-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.wptpnAdmin?.nonce || '',
                },
                body: JSON.stringify({ bot_token: botToken }),
            });

            const data = await response.json();
            setTestResult({
                success: data.success,
                message: data.message,
            });
        } catch (error) {
            setTestResult({
                success: false,
                message: error instanceof Error ? error.message : 'Unknown error',
            });
        } finally {
            setTesting(false);
        }
    };

    return (
        <div className="wptpn-connection-tab">
            <Card>
                <CardHeader>
                    <h2>{__('Bot Configuration', 'wp-telegram-post-notifier')}</h2>
                </CardHeader>
                <CardBody>
                    <p>
                        {__(
                            'Configure your Telegram bot token to enable notifications. You can get a bot token from @BotFather on Telegram.',
                            'wp-telegram-post-notifier'
                        )}
                    </p>

                    <TextControl
                        label={__('Bot Token', 'wp-telegram-post-notifier')}
                        value={showToken ? botToken : '••••••••••••••••••••••••••••••••'}
                        onChange={setBotToken}
                        type={showToken ? 'text' : 'password'}
                        help={__(
                            'Enter your Telegram bot token. This will be used to send messages to your channels/chats.',
                            'wp-telegram-post-notifier'
                        )}
                        aria-label={__('Telegram Bot Token', 'wp-telegram-post-notifier')}
                    />

                    <div className="wptpn-token-actions">
                        <Button
                            variant="secondary"
                            onClick={() => setShowToken(!showToken)}
                            aria-label={showToken ? __('Hide bot token', 'wp-telegram-post-notifier') : __('Reveal bot token', 'wp-telegram-post-notifier')}
                        >
                            {showToken ? __('Hide', 'wp-telegram-post-notifier') : __('Reveal', 'wp-telegram-post-notifier')}
                        </Button>
                    </div>

                    <div className="wptpn-connection-actions">
                        <Button
                            variant="primary"
                            onClick={handleSave}
                            disabled={!botToken}
                        >
                            {__('Save Bot Token', 'wp-telegram-post-notifier')}
                        </Button>

                        <Button
                            variant="secondary"
                            onClick={handleTestConnection}
                            disabled={!botToken || testing}
                            isBusy={testing}
                        >
                            {testing ? __('Testing...', 'wp-telegram-post-notifier') : __('Test Connection', 'wp-telegram-post-notifier')}
                        </Button>
                    </div>

                    {testResult && (
                        <Notice
                            status={testResult.success ? 'success' : 'error'}
                            isDismissible={false}
                        >
                            {testResult.message}
                        </Notice>
                    )}
                </CardBody>
            </Card>

            <Card>
                <CardHeader>
                    <h2>{__('Getting Started', 'wp-telegram-post-notifier')}</h2>
                </CardHeader>
                <CardBody>
                    <ol>
                        <li>
                            {__(
                                'Open Telegram and search for @BotFather',
                                'wp-telegram-post-notifier'
                            )}
                        </li>
                        <li>
                            {__(
                                'Send /newbot command and follow the instructions',
                                'wp-telegram-post-notifier'
                            )}
                        </li>
                        <li>
                            {__(
                                'Copy the bot token and paste it above',
                                'wp-telegram-post-notifier'
                            )}
                        </li>
                        <li>
                            {__(
                                'Add your bot to your channel/group and make it an admin',
                                'wp-telegram-post-notifier'
                            )}
                        </li>
                        <li>
                            {__(
                                'Test the connection using the button above',
                                'wp-telegram-post-notifier'
                            )}
                        </li>
                    </ol>
                </CardBody>
            </Card>
        </div>
    );
};

export default ConnectionTab;
