<?php

/**
 * Created by: sebastien
 * Date: 08/02/2025
 * Time: 17:40.
 */

namespace App\Service;

use App\Entity\Article;
use App\Entity\Favorite;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;

class FavoriteService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FavoriteRepository $favoriteRepository,
    ) {
    }

    public function addArticleToUserFavorites(Article $article, User $user): void
    {
        $existingFavorite = $this->favoriteRepository->findOneBy(['article' => $article, 'reader' => $user]);

        if (null !== $existingFavorite) {
            return;
        }

        $favorite = new Favorite($user, $article);
        $this->entityManager->persist($favorite);
        $this->entityManager->flush();
    }
}
