import React from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Notice } from '@wordpress/components';
import type { TabComponentProps } from '../types';

const AdvancedTab: React.FC<TabComponentProps> = ({ settings, onUpdate }) => {
    return (
        <div className="wptpn-advanced-tab">
            <Card>
                <CardHeader>
                    <h2>{__('Advanced Settings', 'wp-telegram-post-notifier')}</h2>
                </CardHeader>
                <CardBody>
                    <Notice status="info">
                        {__('Advanced settings feature coming soon. This will include rate limiting, retry settings, and other advanced configurations.', 'wp-telegram-post-notifier')}
                    </Notice>
                </CardBody>
            </Card>
        </div>
    );
};

export default AdvancedTab;
