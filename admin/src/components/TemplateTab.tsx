import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Button, TextareaControl, Card, CardBody, CardHeader, Notice } from '@wordpress/components';
import type { TabComponentProps } from '../types';

const TemplateTab: React.FC<TabComponentProps> = ({ settings, onUpdate }) => {
    const [template, setTemplate] = useState(settings.template || '');
    const [preview, setPreview] = useState('');

    const handleSave = () => {
        onUpdate({ ...settings, template });
    };

    const handlePreview = async () => {
        try {
            const response = await fetch('/wp-json/wptpn/v1/preview-template', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.wptpnAdmin?.nonce || '',
                },
                body: JSON.stringify({ template }),
            });

            const data = await response.json();
            if (data.success) {
                setPreview(data.data.previews.MarkdownV2 || '');
            }
        } catch (error) {
            console.error('Preview failed:', error);
        }
    };

    return (
        <div className="wptpn-template-tab">
            <Card>
                <CardHeader>
                    <h2>{__('Message Template', 'wp-telegram-post-notifier')}</h2>
                </CardHeader>
                <CardBody>
                    <p>
                        {__(
                            'Customize the message template that will be sent to Telegram. Use tokens to insert dynamic content.',
                            'wp-telegram-post-notifier'
                        )}
                    </p>

                    <TextareaControl
                        label={__('Template', 'wp-telegram-post-notifier')}
                        value={template}
                        onChange={setTemplate}
                        rows={10}
                        help={__(
                            'Available tokens: {post_title}, {post_excerpt}, {post_url}, {site_name}, {post_author}, {post_date}, {categories}, {tags}',
                            'wp-telegram-post-notifier'
                        )}
                    />

                    <div className="wptpn-template-actions">
                        <Button variant="primary" onClick={handleSave}>
                            {__('Save Template', 'wp-telegram-post-notifier')}
                        </Button>
                        <Button variant="secondary" onClick={handlePreview}>
                            {__('Preview', 'wp-telegram-post-notifier')}
                        </Button>
                    </div>

                    {preview && (
                        <div className="wptpn-template-preview">
                            <h3>{__('Preview:', 'wp-telegram-post-notifier')}</h3>
                            <pre>{preview}</pre>
                        </div>
                    )}
                </CardBody>
            </Card>
        </div>
    );
};

export default TemplateTab;
