<?php
/**
 * Created by: sebastien
 * Date: 18/02/2024
 * Time: 22:41
 */

namespace App\Controller\htmx;

use App\Entity\Favorite;
use App\Repository\ArticleRepository;
use App\Repository\FavoriteRepository;
use App\Service\FavoriteService;
use Doctrine\ORM\EntityManagerInterface;
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
    const array ButtonSize = [
        'Default' => 'small',
        'Small' => 'small',
        'Large' => 'large'];

    public function __construct(private ArticleRepository      $articleRepository,
                                private FavoriteRepository     $favoriteRepository,
                                private EntityManagerInterface $entityManager,
                                private FavoriteService $favoriteService)
    {
    }

    #[Route('/{slug}/favorite', methods: ['POST'])]
    public function addFavoriteArticle(string $slug, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $renderWithSize = $request->query->get('format') ?? self::ButtonSize['Default'];

        $article = $this->articleRepository->findOneBy(['slug' => $slug]);
        //TODO success message
        $this->favoriteService->addArticleToUserFavorites($article, $this->getUser());

        //default is small button
        return $this->render('components/favorite-button.html.twig', [
            'article' => $article,
            'format' => $renderWithSize]);
    }

    //TODO remove if already favorite
    #[Route('/{slug}/favorite', methods: ['DELETE'])]
    public function removeFavoriteArticle(string $slug): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $article = $this->articleRepository->findOneBy(['slug' => $slug]);
        $favorite = $this->favoriteRepository->findOneBy(['article' => $article, 'reader' => $this->getUser()]);

        //TODO success / error message
        if ($favorite) {
            $this->entityManager->remove($favorite);
            $this->entityManager->flush();
        }

        return $this->render('components/favorite-button.html.twig', ['article' => $article, 'format' => 'large']);
    }
}
