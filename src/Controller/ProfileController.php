<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Service\FollowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ArticleRepository $articleRepository,
        private readonly FollowService $followService,
        #[Autowire(env: 'ELEMENT_PER_PAGE')]
        private readonly int $elementPerPage,
    ) {
    }

    #[Route('/profile/{username}', name: 'app_profile')]
    public function index(string $username, Request $request): Response
    {
        $userWatched = $this->userRepository->findOneBy(['username' => $username]);

        $page = (int) $request->query->get('page', 1);
        $page <= 1 && $page = 1;

        $paginatedArticles = $this->articleRepository->findByUserPaginated($userWatched, $page, $this->elementPerPage);

        return $this->render('profile/index.html.twig', [
            'tab' => 'all',
            'user' => $userWatched,
            'paginatedArticles' => $paginatedArticles,
            'currentPage' => $page,
            'lastPage' => ceil($paginatedArticles->count() / $this->elementPerPage),
            'following' => $this->followService->isFollowing($this->getUser(), $userWatched),
        ]);
    }

    #[Route('/profile/{username}/favorited', name: 'app_profile_favoritedarticles')]
    public function favoritedArticles(string $username, Request $request): Response
    {
        $userWatched = $this->userRepository->findOneBy(['username' => $username]);

        $page = (int) $request->query->get('page', 1);
        $page <= 1 && $page = 1;

        $paginatedArticles = $this->articleRepository->findByUserFavoritedPaginated($userWatched, $page, $this->elementPerPage);

        return $this->render('profile/index.html.twig', [
            'tab' => 'favorited',
            'user' => $userWatched,
            'paginatedArticles' => $paginatedArticles,
            'currentPage' => $page,
            'lastPage' => ceil($paginatedArticles->count() / $this->elementPerPage),
            'following' => $this->followService->isFollowing($this->getUser(), $userWatched),
        ]);
    }
}
