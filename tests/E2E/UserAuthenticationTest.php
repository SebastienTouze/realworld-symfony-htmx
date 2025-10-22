<?php

namespace App\Tests\E2E;

class UserAuthenticationTest extends E2ETestCase
{
    /**
     * Test complete user authentication workflow:
     * - Sign up
     * - Login
     * - Navigate to settings
     * - Update settings
     * - Logout
     * - Verify protected route access is blocked
     */
    public function testCompleteUserAuthenticationWorkflow(): void
    {
        // Generate unique credentials for this test run
        $timestamp = time();
        $username = "testuser_{$timestamp}";
        $email = "testuser_{$timestamp}@example.com";
        $password = 'TestPassword123!';

        // Step 1: Navigate to registration page
        $this->client->request('GET', '/register');
        $crawler = $this->client->getCrawler();

        // Verify page loads with "Sign up" heading
        $this->assertSelectorTextContains('h1', 'Sign up');

        // Step 2: Register new user
        $form = $crawler->selectButton('Sign up')->form([
            'registration_form[username]' => $username,
            'registration_form[email]' => $email,
            'registration_form[plainPassword]' => $password,
        ]);

        $this->client->submit($form);

        // Wait for redirect and verify we're on home page
        $this->client->waitFor('body');
        $currentUrl = $this->client->getCurrentURL();
        $this->assertStringContainsString('/', $currentUrl);

        // Step 3: Login with new credentials
        $this->client->request('GET', '/login');
        $crawler = $this->client->getCrawler();

        $this->assertSelectorTextContains('h1', 'Sign in');

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => $email,
            '_password' => $password,
        ]);

        $this->client->submit($form);
        $this->client->waitFor('body');

        // Verify redirect to home page and user is authenticated
        // Check for user menu or navigation elements that only appear when logged in
        $this->client->waitFor('a[href="/settings"]', 10);
        $this->assertSelectorExists('a[href="/settings"]');

        // Step 4: Navigate to settings page
        $this->client->request('GET', '/settings');
        $this->client->waitFor('body');

        // Verify "Your Settings" heading is visible
        $this->assertSelectorTextContains('h1', 'Your Settings');

        // Verify form is populated with user data
        $crawler = $this->client->getCrawler();
        $usernameInput = $crawler->filter('input[name="settings[username]"]');
        $this->assertCount(1, $usernameInput);
        $this->assertSame($username, $usernameInput->attr('value'));

        // Step 5: Update settings - change bio field
        $newBio = 'Updated bio at '.date('Y-m-d H:i:s');

        $form = $crawler->selectButton('Update Settings')->form([
            'settings[bio]' => $newBio,
        ]);

        $this->client->submit($form);

        // Wait for HTMX to complete (settings uses HTMX for form submission)
        $this->waitForHtmx();

        // Wait for success toast message
        $this->client->waitFor('.toast', 5);

        // Verify success message appears
        $pageContent = $this->client->getPageSource();
        $this->assertStringContainsString('Settings updated successfully', $pageContent);

        // Refresh page to verify changes are persisted
        $this->client->request('GET', '/settings');
        $this->client->waitFor('body');

        $crawler = $this->client->getCrawler();
        $bioTextarea = $crawler->filter('textarea[name="settings[bio]"]');
        $this->assertCount(1, $bioTextarea);
        $this->assertSame($newBio, $bioTextarea->text());

        // Step 6: Logout
        $crawler = $this->client->getCrawler();
        $logoutLink = $crawler->filter('a[href="/logout"]');
        $this->assertCount(1, $logoutLink, 'Logout link should be present');

        $this->client->request('GET', '/logout');
        $this->client->waitFor('body');

        // Verify redirect to home page
        $currentUrl = $this->client->getCurrentURL();
        $this->assertStringContainsString('/', $currentUrl);

        // Verify user is logged out - navbar should show login/register links
        $this->client->waitFor('a[href="/login"]', 10);
        $this->assertSelectorExists('a[href="/login"]');
        $this->assertSelectorExists('a[href="/register"]');

        // Step 7: Attempt to visit /settings - should redirect to login
        $this->client->request('GET', '/settings');
        $this->client->waitFor('body');

        // Should be redirected to login page
        $currentUrl = $this->client->getCurrentURL();
        $this->assertStringContainsString('/login', $currentUrl);
        $this->assertSelectorTextContains('h1', 'Sign in');
    }
}
