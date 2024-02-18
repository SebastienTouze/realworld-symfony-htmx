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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('htmx/articles')]
class ArticleHtmxController extends AbstractController {

    public function __construct(private ArticleRepository      $articleRepository,
                                private FavoriteRepository     $favoriteRepository,
                                private EntityManagerInterface $entityManager) {
    }

    #[Route('/{slug}/favorite', methods: ['POST'])]
    public function addFavoriteArticle(string $slug): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $article = $this->articleRepository->findOneBy(['slug' => $slug]);
        //TODO test if article is already favorited and post a message, currently user can favorite unlimited articles
        //TODO success message
        $favorite = new Favorite($this->getUser(), $article);

        $this->entityManager->persist($favorite);
        $this->entityManager->flush();

        return $this->render('home/components/favorite-button.html.twig', ['article' => $article]);
    }

    #[Route('/{slug}/favorite', methods: ['DELETE'])]
    public function removeFavoriteArticle(string $slug): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $article = $this->articleRepository->findOneBy(['slug' => $slug]);
        $favorite = $this->favoriteRepository->findOneBy(['article' => $article, 'reader' => $this->getUser()]);

        //TODO success / error message
        if($favorite) {
            $this->entityManager->remove($favorite);
            $this->entityManager->flush();
        }

        return $this->render('home/components/favorite-button.html.twig', ['article' => $article]);
    }
}
