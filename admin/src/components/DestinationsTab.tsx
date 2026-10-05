import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Button, TextControl, Card, CardBody, CardHeader, Notice } from '@wordpress/components';
import type { TabComponentProps, Destination } from '../types';

const DestinationsTab: React.FC<TabComponentProps> = ({ settings, onUpdate }) => {
    const [destinations, setDestinations] = useState<Destination[]>(settings.destinations || []);
    const [newDestination, setNewDestination] = useState({ name: '', chat_id: '', template_id: 'default', enabled: true });

    const addDestination = () => {
        if (!newDestination.name || !newDestination.chat_id) {
            return;
        }

        const destination = {
            ...newDestination,
            id: Date.now().toString(),
        };

        const updated = [...destinations, destination];
        setDestinations(updated);
        onUpdate({ ...settings, destinations: updated });
        setNewDestination({ name: '', chat_id: '', template_id: 'default', enabled: true });
    };

    const removeDestination = (id: string) => {
        const updated = destinations.filter((dest) => dest.id !== id);
        setDestinations(updated);
        onUpdate({ ...settings, destinations: updated });
    };

    const toggleDestination = (id: string) => {
        const updated = destinations.map((dest) =>
            dest.id === id ? { ...dest, enabled: !dest.enabled } : dest
        );
        setDestinations(updated);
        onUpdate({ ...settings, destinations: updated });
    };

    return (
        <div className="wptpn-destinations-tab">
            <Card>
                <CardHeader>
                    <h2>{__('Destinations', 'wp-telegram-post-notifier')}</h2>
                </CardHeader>
                <CardBody>
                    <p>
                        {__(
                            'Add channels or chats where you want to send notifications. You can add multiple destinations.',
                            'wp-telegram-post-notifier'
                        )}
                    </p>

                    <div className="wptpn-add-destination">
                        <TextControl
                            label={__('Name', 'wp-telegram-post-notifier')}
                            value={newDestination.name}
                            onChange={(value) => setNewDestination({ ...newDestination, name: value })}
                            placeholder={__('e.g., My Channel', 'wp-telegram-post-notifier')}
                        />

                        <TextControl
                            label={__('Chat ID', 'wp-telegram-post-notifier')}
                            value={newDestination.chat_id}
                            onChange={(value) => setNewDestination({ ...newDestination, chat_id: value })}
                            placeholder={__('e.g., @mychannel or -1001234567890', 'wp-telegram-post-notifier')}
                            help={__(
                                'Use @channelname for public channels or -1001234567890 for private channels/groups',
                                'wp-telegram-post-notifier'
                            )}
                        />

                        <Button variant="primary" onClick={addDestination}>
                            {__('Add Destination', 'wp-telegram-post-notifier')}
                        </Button>
                    </div>

                    <div className="wptpn-destinations-list">
                        {destinations.length === 0 ? (
                            <Notice status="info">
                                {__('No destinations configured. Add one above to get started.', 'wp-telegram-post-notifier')}
                            </Notice>
                        ) : (
                            destinations.map((destination) => (
                                <div key={destination.id} className="wptpn-destination-item">
                                    <div className="wptpn-destination-info">
                                        <strong>{destination.name}</strong>
                                        <span className="wptpn-destination-chat-id">{destination.chat_id}</span>
                                        <span className={`wptpn-destination-status ${destination.enabled ? 'enabled' : 'disabled'}`}>
                                            {destination.enabled ? __('Enabled', 'wp-telegram-post-notifier') : __('Disabled', 'wp-telegram-post-notifier')}
                                        </span>
                                    </div>
                                    <div className="wptpn-destination-actions">
                                        <Button
                                            variant="secondary"
                                            onClick={() => toggleDestination(destination.id)}
                                        >
                                            {destination.enabled ? __('Disable', 'wp-telegram-post-notifier') : __('Enable', 'wp-telegram-post-notifier')}
                                        </Button>
                                        <Button
                                            variant="secondary"
                                            isDestructive
                                            onClick={() => removeDestination(destination.id)}
                                        >
                                            {__('Remove', 'wp-telegram-post-notifier')}
                                        </Button>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </CardBody>
            </Card>
        </div>
    );
};

export default DestinationsTab;
