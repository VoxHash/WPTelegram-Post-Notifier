import React from 'react';
import { render } from '@wordpress/element';
import { Provider } from '@wordpress/data';
import App from './components/App';
import './styles/index.css';

// Initialize the admin app
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('wptpn-admin-app');
    if (container) {
        render(
            <Provider>
                <App />
            </Provider>,
            container
        );
    }
});
