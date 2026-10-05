import React from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Notice } from '@wordpress/components';
import type { TabComponentProps } from '../types';

const DeliveryTab: React.FC<TabComponentProps> = ({ settings, onUpdate }) => {
    return (
        <div className="wptpn-delivery-tab">
            <Card>
                <CardHeader>
                    <h2>{__('Delivery Options', 'wp-telegram-post-notifier')}</h2>
                </CardHeader>
                <CardBody>
                    <Notice status="info">
                        {__('Delivery options feature coming soon. This will include settings for parse mode, silent notifications, and other delivery preferences.', 'wp-telegram-post-notifier')}
                    </Notice>
                </CardBody>
            </Card>
        </div>
    );
};

export default DeliveryTab;
