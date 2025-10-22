<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        #[Autowire(env: 'ELEMENT_PER_PAGE')]
        private readonly int $elementPerPage,
    ) {
    }

    #[Route('/profile/{username:user}', name: 'app_profile')]
    public function index(User $user, Request $request): Response
    {
        $page = (int) $request->query->get('page', '1');
        if ($page <= 1) {
            $page = 1;
        }

        $paginatedArticles = $this->articleRepository->findByUserPaginated($user, $page, $this->elementPerPage);

        return $this->render('profile/index.html.twig', [
            'tab' => 'all',
            'user' => $user,
            'paginatedArticles' => $paginatedArticles,
            'currentPage' => $page,
            'lastPage' => ceil($paginatedArticles->count() / $this->elementPerPage),
        ]);
    }

    #[Route('/profile/{username:user}/favorited', name: 'app_profile_favoritedarticles')]
    public function favoritedArticles(User $user, Request $request): Response
    {
        $page = (int) $request->query->get('page', '1');
        if ($page <= 1) {
            $page = 1;
        }

        $paginatedArticles = $this->articleRepository->findByUserFavoritedPaginated($user, $page, $this->elementPerPage);

        return $this->render('profile/index.html.twig', [
            'tab' => 'favorited',
            'user' => $user,
            'paginatedArticles' => $paginatedArticles,
            'currentPage' => $page,
            'lastPage' => ceil($paginatedArticles->count() / $this->elementPerPage),
        ]);
    }
}
