import { expect, test } from '@playwright/test';

test('the real Statamic Control Panel serves its login screen', async ({ page }) => {
    await page.goto('/cp/utilities/figma-assets');

    await expect(page).toHaveURL(/\/cp\/auth\/login$/);
    await expect(page.getByRole('heading', { name: 'Sign in with email' })).toBeVisible();
    await expect(page.locator('input').nth(0)).toBeVisible();
    await expect(page.locator('input').nth(1)).toBeVisible();
});
