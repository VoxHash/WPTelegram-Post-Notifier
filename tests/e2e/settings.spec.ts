import { test, expect } from '@playwright/test';

test.describe('WP Telegram Post Notifier Settings', () => {
    test.beforeEach(async ({ page }) => {
        // Navigate to the settings page
        await page.goto('/wp-admin/options-general.php?page=wptpn-settings');
    });

    test('should load the settings page', async ({ page }) => {
        await expect(page.locator('h1')).toContainText('WP Telegram Post Notifier Settings');
    });

    test('should have connection tab', async ({ page }) => {
        await expect(page.locator('.tab-connection')).toBeVisible();
    });

    test('should have destinations tab', async ({ page }) => {
        await expect(page.locator('.tab-destinations')).toBeVisible();
    });

    test('should have template tab', async ({ page }) => {
        await expect(page.locator('.tab-template')).toBeVisible();
    });

    test('should have logs tab', async ({ page }) => {
        await expect(page.locator('.tab-logs')).toBeVisible();
    });
});
