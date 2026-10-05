import React from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Notice } from '@wordpress/components';
import type { TabComponentProps } from '../types';

const RoutingTab: React.FC<TabComponentProps> = ({ settings, onUpdate }) => {
    return (
        <div className="wptpn-routing-tab">
            <Card>
                <CardHeader>
                    <h2>{__('Routing Rules', 'wp-telegram-post-notifier')}</h2>
                </CardHeader>
                <CardBody>
                    <Notice status="info">
                        {__('Routing rules feature coming soon. This will allow you to send different posts to different channels based on categories, tags, or post types.', 'wp-telegram-post-notifier')}
                    </Notice>
                </CardBody>
            </Card>
        </div>
    );
};

export default RoutingTab;
