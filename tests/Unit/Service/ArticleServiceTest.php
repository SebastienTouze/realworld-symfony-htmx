<?php

namespace App\Tests\Unit\Service;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\ArticleService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ArticleServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private ArticleService $articleService;
    private User $mockUser;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);

        // Create mock user
        $this->mockUser = new User();
        $this->mockUser->setUsername('testuser');
        $this->mockUser->setEmail('test@example.com');

        // Configure security mock to return our mock user
        $this->security->method('getUser')->willReturn($this->mockUser);

        // Create service instance
        $this->articleService = new ArticleService($this->entityManager, $this->security);
    }

    /**
     * Data provider for slug generation tests.
     */
    public static function slugGenerationProvider(): array
    {
        return [
            'simple title' => ['Hello World', 'hello-world'],
            'unicode characters' => ['Café François à Paris', 'cafe-francois-a-paris'],
            'special characters' => ['Hello@World#2024!!! Test???', 'hello-world-2024-test'],
            'leading and trailing spaces & multiple consecutive spaces' => ['  Hello    World  test    ', 'hello-world-test'],
            'length limitation' => [str_repeat('a', 300), str_repeat('a', 250)],
        ];
    }

    #[DataProvider('slugGenerationProvider')]
    public function testGenerateSlugFromTitle(string $input, string $expected): void
    {
        // Create article with title
        $article = new Article();
        $article->setTitle($input);
        $article->setBody('Test body');

        // Configure entity manager to expect persist and flush
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        // Call createOrUpdateArticle which triggers slug generation
        $this->articleService->createOrUpdateArticle($article);

        // Assert slug was generated correctly
        $this->assertEquals($expected, $article->getSlug());
    }

    public function testGenerateSlugThrowsExceptionForEmptyTitle(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Article title can\'t be empty');

        $article = new Article();
        $article->setTitle('');
        $article->setBody('Test body');

        $this->articleService->createOrUpdateArticle($article);
    }

    public function testGenerateSlugWithWhitespaceOnlyTitleThrowsException(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Article title can\'t be empty');

        $article = new Article();
        $article->setTitle('   ');
        $article->setBody('Test body');

        $this->articleService->createOrUpdateArticle($article);
    }

    public function testCreateOrUpdateArticleSetsAuthor(): void
    {
        $article = new Article();
        $article->setTitle('Test Article');
        $article->setBody('Test body');

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->articleService->createOrUpdateArticle($article);

        // Verify author was set to the current user
        $this->assertSame($this->mockUser, $article->getAuthor());
    }

    public function testAddCommentSetsAuthorAndTimestamp(): void
    {
        // Create article with required fields
        $article = new Article();
        $article->setTitle('Test Article');
        $article->setBody('Test body');
        $article->setAuthor($this->mockUser);

        $this->articleService->createOrUpdateArticle($article);

        // Create comment
        $comment = new Comment();
        $comment->setBody('This is a test comment');

        // Comment should be persisted in DB
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->articleService->addComment($comment, $article);

        $this->assertSame($this->mockUser, $comment->getAuthor());
        $this->assertSame($article, $comment->getArticle());

        // Verify createdAt is set
        $this->assertNotNull($comment->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $comment->getCreatedAt());

        // Verify createdAt is recent (within last second)
        $now = new \DateTimeImmutable();
        $diff = $now->getTimestamp() - $comment->getCreatedAt()->getTimestamp();
        $this->assertLessThanOrEqual(1, $diff);
    }
}
