import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/Browser',
    fullyParallel: false,
    reporter: 'list',
    use: {
        baseURL: 'http://127.0.0.1:8000',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    webServer: {
        command: './vendor/bin/testbench serve --host=127.0.0.1 --port=8000 --no-reload',
        url: 'http://127.0.0.1:8000/cp/auth/login',
        reuseExistingServer: !process.env.CI,
    },
});