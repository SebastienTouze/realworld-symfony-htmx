<?php

/**
 * Created by: sebastien
 * Date: 18/02/2024
 * Time: 22:41.
 */

namespace App\Controller\htmx;

use App\Repository\ArticleRepository;
use App\Repository\FavoriteRepository;
use App\Service\FavoriteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('htmx/articles')]
class ArticleFavoriteButtonController extends AbstractController
{
    /**
     * @var array<string, string>
     */
    public const array ButtonSize = [
        'Default' => 'small',
        'Small' => 'small',
        'Large' => 'large'];

    public function __construct(private ArticleRepository $articleRepository,
        private FavoriteRepository $favoriteRepository,
        private FavoriteService $favoriteService)
    {
    }

    #[Route('/{id}/favorite/{format}', requirements: ['format' => '\w+'], methods: ['POST'])]
    public function addFavoriteArticle(int $id, string $format): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $renderWithSize = $this->getFormatFromRequest($format);

        $article = $this->articleRepository->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        try {
            $this->favoriteService->addArticleToUserFavorites($article, $this->getUser());

            return $this->render('components/favorite-button.html.twig', [
                'article' => $article,
                'format' => $renderWithSize,
                'isFavorited' => true,
                'toastMessage' => 'Article favorited successfully!',
                'toastType' => 'success',
            ]);
        } catch (\Exception $e) {
            return $this->render('components/favorite-button.html.twig', [
                'article' => $article,
                'format' => $renderWithSize,
                'isFavorited' => false,
                'toastMessage' => 'Failed to favorite article',
                'toastType' => 'error',
            ]);
        }
    }

    #[Route('/{id}/favorite/{format}', requirements: ['format' => '\w+'], methods: ['GET'])]
    public function getFavoriteButton(int $id, string $format, Request $request): Response
    {
        $format = $this->getFormatFromRequest($format);

        $article = $this->articleRepository->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        // if user is connected, checks the favorite status
        $user = $this->getUser();
        $isFavorited = false;

        if (null !== $user) {
            $favorite = $this->favoriteRepository->findOneBy([
                'article' => $article,
                'reader' => $user,
            ]);
            $isFavorited = null !== $favorite;
        }

        // Render button with complete context
        return $this->render('components/favorite-button.html.twig', [
            'article' => $article,
            'format' => $format,
            'isFavorited' => $isFavorited,
        ]);
    }

    #[Route('/{slug}/favorite/{format}', requirements: ['format' => '\w+'], methods: ['DELETE'])]
    public function removeFavoriteArticle(string $slug, string $format): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $format = $this->getFormatFromRequest($format);

        $article = $this->articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        try {
            if ($this->favoriteService->removeArticleFromUserFavorites($article, $this->getUser())) {
                return $this->render('components/favorite-button.html.twig', [
                    'article' => $article,
                    'format' => $format,
                    'isFavorited' => false,
                    'toastMessage' => 'Article removed from favorites',
                    'toastType' => 'success',
                ]);
            }

            return $this->render('components/favorite-button.html.twig', [
                'article' => $article,
                'format' => $format,
                'isFavorited' => false,
                'toastMessage' => 'Article was not in favorites',
                'toastType' => 'error',
            ]);
        } catch (\Exception $e) {
            return $this->render('components/favorite-button.html.twig', [
                'article' => $article,
                'format' => $format,
                'isFavorited' => true,
                'toastMessage' => 'Failed to remove from favorites',
                'toastType' => 'error',
            ]);
        }
    }

    /**
     * Gets the format param from request and validate it, set to Default if not present or invalid.
     */
    private function getFormatFromRequest(string $format): string
    {
        if (!in_array($format, self::ButtonSize, true)) {
            $format = self::ButtonSize['Default'];
        }

        return $format;
    }
}
