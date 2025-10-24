<?php

namespace App\Tests\E2E;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;

class ArticleInteractionTest extends E2ETestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test complete article interaction workflow:
     * - Register author user and create article via UI
     * - Register reader user via UI
     * - Navigate to home page
     * - Click on the article
     * - Like/Favorite the article
     * - Follow the article author
     * - Verify persistence after page refresh
     * - Unfavorite and unfollow
     */
    public function testUserArticleInteractionWorkflow(): void
    {
        $timestamp = time();

        // Step 1: Setup - Register author user and create article, then register reader user
        $authorData = $this->registerAuthorAndCreateArticle($timestamp);
        $this->registerAndLoginReader($timestamp);

        // Step 2: Navigate to home page and verify article is visible
        $this->navigateToHomeAndVerifyArticle($authorData['articleTitle']);

        // Step 3: Click on the article
        $this->openAndValidateArticle($authorData['articleTitle'], $authorData['articleBody'], $authorData['authorUsername']);

        // Step 4: Favorite/Like the article
        $this->favoriteArticle();

        // Step 5: Follow the author
        $this->followAuthor($authorData['authorUsername']);

        // Step 6: Verify persistence - refresh the page
        $this->verifyPersistenceAfterRefresh();

        // Step 7: Test unfollow
        $this->unfollowAuthor();
    }

    /**
     * Register author user, login, create an article, and logout.
     *
     * @return array{authorUsername: string, authorEmail: string, authorPassword: string, articleTitle: string, articleDescription: string, articleBody: string}
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    private function registerAuthorAndCreateArticle(int $timestamp): array
    {
        // Author credentials (the one who will write the article)
        $authorUsername = "author_{$timestamp}";
        $authorEmail = "author_{$timestamp}@example.com";
        $authorPassword = 'TestPassword123!';

        $this->createTestUser($authorUsername, $authorEmail, $authorPassword);
        $this->loginUser($authorEmail, $authorPassword);

        // Verify we're authenticated by checking for settings link
        $this->client->waitFor('a[href="/settings"]', 10);
        $this->assertSelectorExists('a[href="/settings"]');

        // Navigate to create article page and create an article
        $articleTitle = "Test Article {$timestamp}";
        $articleDescription = 'This is a test article description';
        $articleBody = 'This is the test article body content with some interesting information.';

        $this->client->request('GET', '/editor');
        $this->client->waitFor('body');

        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Publish Article')->form([
            'article[title]' => $articleTitle,
            'article[description]' => $articleDescription,
            'article[body]' => $articleBody,
        ]);

        $this->client->submit($form);
        $this->client->waitFor('body');
        usleep(500000); // Wait for article creation

        // Logout after creating the article
        $this->client->request('GET', '/logout');
        $this->client->waitFor('body');

        return [
            'authorUsername' => $authorUsername,
            'authorEmail' => $authorEmail,
            'authorPassword' => $authorPassword,
            'articleTitle' => $articleTitle,
            'articleDescription' => $articleDescription,
            'articleBody' => $articleBody,
        ];
    }

    /**
     * @return array{readerUsername: string, readerEmail: string, readerPassword: string}
     */
    private function registerAndLoginReader(int $timestamp): array
    {
        $readerUsername = "reader_{$timestamp}";
        $readerEmail = "reader_{$timestamp}@example.com";
        $readerPassword = 'TestPassword123!';

        $this->createTestUser($readerUsername, $readerEmail, $readerPassword);
        $this->loginUser($readerEmail, $readerPassword);

        // Verify we're authenticated by checking for settings link
        $this->client->waitFor('a[href="/settings"]', 10);
        $this->assertSelectorExists('a[href="/settings"]');

        return [
            'readerUsername' => $readerUsername,
            'readerEmail' => $readerEmail,
            'readerPassword' => $readerPassword,
        ];
    }

    private function navigateToHomeAndVerifyArticle(string $articleTitle): void
    {
        $this->client->request('GET', '/');
        $this->client->waitFor('body');

        // Verify home page loaded
        $currentUrl = $this->client->getCurrentURL();
        $this->assertStringContainsString('/', $currentUrl);

        // Verify article feed is visible and contains our test article
        $crawler = $this->client->getCrawler();
        $this->assertSelectorTextContains('body', $articleTitle);
    }

    private function openAndValidateArticle(string $articleTitle, string $articleBody, string $authorUsername): void
    {
        $crawler = $this->client->getCrawler();

        // Find the article link by title and click it
        $articleLink = $crawler->filter('a.preview-link')->reduce(function ($node) use ($articleTitle) {
            return str_contains($node->text(), $articleTitle);
        })->first();

        $this->assertCount(1, $articleLink, 'Article link should be found');
        $articleLink->link();
        $this->client->click($articleLink->link());

        // Wait for article page to load
        $this->client->waitFor('body');

        // Verify we're on the article detail page
        $this->assertSelectorTextContains('h1', $articleTitle);
        $this->assertSelectorTextContains('body', $articleBody);
        $this->assertSelectorTextContains('body', $authorUsername);
    }

    private function favoriteArticle(): int
    {
        $crawler = $this->client->getCrawler();

        // Find the favorite button (should be outlined initially, not favorited)
        $favoriteButton = $crawler->filter('button.btn-outline-primary')->reduce(function ($node) {
            return str_contains($node->text(), 'Favorite Post');
        })->first();

        $this->assertCount(1, $favoriteButton, 'Favorite button should be found');

        // Initial favorite count
        $favoriteCountText = $favoriteButton->filter('.counter')->text();
        $initialFavoriteCount = (int) trim($favoriteCountText, '()');

        $favoriteButton->click();
        $this->waitForHtmx();

        // Verify button state changed (now should have btn-primary class)
        $crawler = $this->client->getCrawler();
        $favoritedButton = $crawler->filter('button.btn-primary')->reduce(function ($node) {
            return str_contains($node->text(), 'Favorite Post');
        });

        $this->assertGreaterThan(0, $favoritedButton->count(), 'Favorited button should be found with btn-primary class');

        // Verify favorite count increased
        $newFavoriteCountText = $favoritedButton->first()->filter('.counter')->text();
        $newFavoriteCount = (int) trim($newFavoriteCountText, '()');
        $this->assertEquals($initialFavoriteCount + 1, $newFavoriteCount, 'Favorite count should increase by 1');

        // Verify success toast appears
        $this->client->waitFor('.toast-success', 5);
        $this->assertSelectorExists('.toast-success');
        $pageContent = $this->client->getPageSource();
        $this->assertStringContainsString('Article favorited successfully', $pageContent);

        return $newFavoriteCount;
    }

    private function followAuthor(string $authorUsername): void
    {
        $crawler = $this->client->getCrawler();

        // Find the follow button (should be btn-outline-secondary initially, not following)
        $followButton = $crawler->filter('button.btn-outline-secondary')->reduce(function ($node) {
            return str_contains($node->text(), 'Follow ');
        })->first();

        $this->assertCount(1, $followButton, 'Follow button should be found with Follow text');

        $followButtonText = $followButton->text();
        $this->assertStringContainsString('Follow '.$authorUsername, $followButtonText);

        $followButton->click();
        $this->waitForHtmx();

        // Verify button state changed (now should have btn-secondary class)
        $crawler = $this->client->getCrawler();
        $followingButton = $crawler->filter('button.btn-secondary')->reduce(function ($node) {
            return str_contains($node->text(), 'Following ');
        });

        $this->assertGreaterThan(0, $followingButton->count(), 'Following button should be found with btn-secondary class and Following text');

        // Verify button text changed to "Following"
        $followingButtonText = $followingButton->first()->text();
        $this->assertStringContainsString('Following '.$authorUsername, $followingButtonText);

        // Verify success toast appears
        $this->client->waitFor('.toast-success', 5);
        $pageContent = $this->client->getPageSource();
        $this->assertStringContainsString('You are now following', $pageContent);
    }

    private function verifyPersistenceAfterRefresh(): void
    {
        $this->client->request('GET', $this->client->getCurrentURL());
        $this->client->waitFor('body');

        // Wait a moment for page to fully load
        usleep(500000);

        // Verify article is still favorited (button should have btn-primary class)
        $crawler = $this->client->getCrawler();
        $favoritedButton = $crawler->filter('button.btn-primary')->reduce(function ($node) {
            return str_contains($node->text(), 'Favorite Post');
        });
        $this->assertGreaterThan(0, $favoritedButton->count(), 'Article should still be favorited after refresh');

        // Verify author is still followed (button should have btn-secondary class)
        $followingButton = $crawler->filter('button.btn-secondary')->reduce(function ($node) {
            return str_contains($node->text(), 'Following ');
        });
        $this->assertGreaterThan(0, $followingButton->count(), 'Reader should still be following after refresh');
    }

    private function unfollowAuthor(): void
    {
        $crawler = $this->client->getCrawler();

        // Find the following button (should be btn-secondary, currently following)
        $followingButton = $crawler->filter('button.btn-secondary')->reduce(function ($node) {
            return str_contains($node->text(), 'Following ');
        });

        $followButton = $followingButton->first();
        $followButton->click();
        $this->waitForHtmx();

        // Verify button returned to "Follow" state (btn-outline-secondary)
        $crawler = $this->client->getCrawler();
        $unfollowedButton = $crawler->filter('button.btn-outline-secondary')->reduce(function ($node) {
            return str_contains($node->text(), 'Follow ');
        });
        $this->assertGreaterThan(0, $unfollowedButton->count(), 'Button should return to Follow state');

        $unfollowedButtonText = $unfollowedButton->first()->text();
        $this->assertStringContainsString('Follow ', $unfollowedButtonText);
        $this->assertStringNotContainsString('Following', $unfollowedButtonText);
    }
}
