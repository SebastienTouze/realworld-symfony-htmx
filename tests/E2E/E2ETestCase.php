<?php

namespace App\Tests\E2E;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class E2ETestCase extends PantherTestCase
{
    protected Client $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createPantherClient([
            'browser' => static::CHROME,
        ]);

        $this->entityManager = static::getContainer()
            ->get('doctrine')
            ->getManager();

        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        parent::tearDown();
    }

    /**
     * Clean test database - SQLite, MySQL/MariaDB and PgSQL compatible version.
     *
     * @throws DoctrineException
     */
    protected function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTables();

        // For PostgreSQL, we need to truncate with CASCADE
        if ($platform instanceof PostgreSQLPlatform) {
            foreach ($tables as $table) {
                $tableName = $table->getName();
                if ('doctrine_migration_versions' !== $tableName) {
                    $connection->executeStatement('TRUNCATE TABLE '.$connection->quoteIdentifier($tableName).' CASCADE');
                }
            }
        } elseif ($platform instanceof SqlitePlatform) {
            // SQLite - disable foreign keys temporarily
            $connection->executeStatement('PRAGMA foreign_keys = OFF');

            foreach ($tables as $table) {
                $tableName = $table->getName();
                if ('doctrine_migration_versions' !== $tableName) {
                    $connection->executeStatement('DELETE FROM '.$connection->quoteIdentifier($tableName));
                }
            }

            $connection->executeStatement('PRAGMA foreign_keys = ON');
        } elseif ($platform instanceof MySQLPlatform) {
            // MySQL - disable foreign key checks temporarily
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $table) {
                $tableName = $table->getName();
                if ('doctrine_migration_versions' !== $tableName) {
                    $connection->executeStatement('TRUNCATE TABLE '.$connection->quoteIdentifier($tableName));
                }
            }

            $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
        } else {
            // Default fallback
            foreach ($tables as $table) {
                $tableName = $table->getName();
                if ('doctrine_migration_versions' !== $tableName) {
                    $connection->executeStatement('DELETE FROM '.$connection->quoteIdentifier($tableName));
                }
            }
        }
    }

    protected function createTestUser(
        string $username,
        string $email,
        string $password = 'TestPassword123!',
    ): User {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword(
            static::getContainer()
                ->get('security.user_password_hasher')
                ->hashPassword($user, $password)
        );
        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function createTestArticle(
        User $author,
        string $title,
        string $description = 'Test description',
        string $body = 'Test body content',
    ): Article {
        $article = new Article();
        $article->setTitle($title);
        $article->setSlug($this->slugify($title));
        $article->setDescription($description);
        $article->setBody($body);
        $article->setAuthor($author);

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $article;
    }

    private function slugify(string $string): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }

    protected function waitForHtmx(int $timeoutMs = 3000): void
    {
        usleep(500000); // 500ms - basic wait for HTMX
    }

    /**
     * Take screenshot on failure (useful for debugging).
     */
    protected function onNotSuccessfulTest(\Throwable $t): never
    {
        if (isset($this->client)) {
            $this->takeScreenshotIfTestFailed();
        }

        parent::onNotSuccessfulTest($t);
    }

    protected function takeScreenshot(string $filename): void
    {
        $screenshotDir = static::getContainer()->getParameter('panther_screenshot_dir');
        if (!is_dir($screenshotDir)) {
            mkdir($screenshotDir, 0777, true);
        }

        $filepath = $screenshotDir.'/'.$filename.'.png';
        $this->client->takeScreenshot($filepath);
    }

    /**
     * Login user via form.
     */
    protected function loginUser(string $email, string $password): void
    {
        $this->client->request('GET', '/login');

        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Sign in')->form([
            '_username' => $email,
            '_password' => $password,
        ]);

        $this->client->submit($form);
        $this->client->waitFor('body'); // Wait for page to load
    }
}
