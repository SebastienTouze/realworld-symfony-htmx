<?php

namespace App\Tests\Unit\Service;

use App\Entity\Article;
use App\Entity\Favorite;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use App\Service\FavoriteService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class FavoriteServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private FavoriteRepository $favoriteRepository;
    private FavoriteService $favoriteService;
    private User $user;
    private Article $article;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->favoriteRepository = $this->createMock(FavoriteRepository::class);

        // Create service instance
        $this->favoriteService = new FavoriteService($this->entityManager, $this->favoriteRepository);

        // Create test entities
        $this->user = new User();
        $this->user->setUsername('testuser');
        $this->user->setEmail('test@example.com');

        $this->article = new Article();
        $this->article->setTitle('Test Article');
        $this->article->setBody('Test body');
        $this->article->setAuthor($this->user);
    }

    public function testAddArticleToUserFavorites(): void
    {
        $user = $this->user;
        $article = $this->article;

        // No existing favorite
        $this->favoriteRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['article' => $article, 'reader' => $user])
            ->willReturn(null);

        // Expects a new favorite with the user and reader defined above
        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->callback(function ($favorite) use ($user, $article) {
                return $favorite instanceof Favorite
                    && $favorite->getReader() === $user
                    && $favorite->getArticle() === $article;
            }));
        $this->entityManager->expects($this->once())->method('flush');

        $this->favoriteService->addArticleToUserFavorites($article, $user);
    }

    public function testAddDuplicateFavoriteIsIdempotent(): void
    {
        $user = $this->user;
        $article = $this->article;
        $existingFavorite = new Favorite($user, $article);

        $this->favoriteRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['article' => $article, 'reader' => $user])
            ->willReturn($existingFavorite);

        // No persisting in DB
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->favoriteService->addArticleToUserFavorites($article, $user);
    }

    public function testRemoveArticleFromUserFavorites(): void
    {
        $user = $this->user;
        $article = $this->article;
        $favorite = new Favorite($user, $article);

        $this->favoriteRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['article' => $article, 'reader' => $user])
            ->willReturn($favorite);

        // Favorite is removed
        $this->entityManager->expects($this->once())->method('remove')->with($favorite);
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->favoriteService->removeArticleFromUserFavorites($article, $user);

        $this->assertTrue($result);
    }

    public function testRemoveNonExistentFavoriteReturnsFalse(): void
    {
        $user = $this->user;
        $article = $this->article;

        // Mock repository to return null (no favorite exists)
        $this->favoriteRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['article' => $article, 'reader' => $user])
            ->willReturn(null);

        // No persisting in DB
        $this->entityManager->expects($this->never())->method('remove');
        $this->entityManager->expects($this->never())->method('flush');

        $result = $this->favoriteService->removeArticleFromUserFavorites($article, $user);

        $this->assertFalse($result);
    }

    public function testMultipleUsersFavoriteSameArticle(): void
    {
        $user = $this->user;
        $article = $this->article;
        $user2 = new User();

        // No existing favorites
        $this->favoriteRepository
            ->method('findOneBy')
            ->willReturn(null);

        // Verify both favorites are persisted independently
        $this->entityManager->expects($this->exactly(2))->method('persist')
            ->with($this->callback(function ($favorite) use ($user, $user2, $article) {
                return $favorite instanceof Favorite
                    && ($favorite->getReader() === $user || $favorite->getReader() === $user2)
                    && $favorite->getArticle() === $article;
            }));
        $this->entityManager->expects($this->exactly(2))->method('flush');

        $this->favoriteService->addArticleToUserFavorites($article, $user);
        $this->favoriteService->addArticleToUserFavorites($article, $user2);
    }
}
