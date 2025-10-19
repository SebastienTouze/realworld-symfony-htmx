<?php

namespace App\Controller\htmx;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FollowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('htmx/author')]
class FollowButtonController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FollowService $followService,
    ) {
    }

    #[Route('/{username}/follow', methods: ['GET'])]
    public function getFollowButton(string $username): Response
    {
        // Find author with error handling
        $author = $this->userRepository->findOneBy(['username' => $username]);
        if (!$author) {
            throw $this->createNotFoundException('Author not found');
        }

        // Determine user authentication and follow status
        $user = $this->getUser();
        $isFollowing = false;

        if (null !== $user) {
            $isFollowing = $this->followService->isFollowing($user, $author);
        }

        // Render button with complete context
        return $this->render('components/follow-button.html.twig', [
            'author' => $author,
            'isFollowing' => $isFollowing,
        ]);
    }

    #[Route('/{username}/unfollow', methods: ['POST'])]
    public function unfollowUser(string $username): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Find author with error handling
        $author = $this->userRepository->findOneBy(['username' => $username]);
        if (!$author) {
            throw $this->createNotFoundException('Author not found');
        }

        $currentUser = $this->getUser();

        try {
            $this->followService->removeUserFromFollowed($currentUser, $author);

            return $this->render('components/follow-button.html.twig', [
                'author' => $author,
                'isFollowing' => false,
                'toastMessage' => 'You are no longer following '.$author->getUsername(),
                'toastType' => 'success',
            ]);
        } catch (\Exception $e) {
            return $this->render('components/follow-button.html.twig', [
                'author' => $author,
                'isFollowing' => true,
                'toastMessage' => 'Failed to unfollow user',
                'toastType' => 'error',
            ]);
        }
    }

    #[Route('/{username:author}/follow', name: 'app_htmx_followbutton_followuser', methods: ['POST'])]
    public function followUser(?User $author, #[CurrentUser] ?User $currentUser): Response|NotFoundHttpException
    {
        if (null === $currentUser) {
            return $this->redirectToRoute('app_login');
        }

        if (null === $author) {
            return $this->createNotFoundException('No author for this username.');
        }

        try {
            $this->followService->addUserToFollowed($currentUser, $author);

            return $this->render('components/follow-button.html.twig', [
                'author' => $author,
                'isFollowing' => true,
                'toastMessage' => 'You are now following '.$author->getUsername(),
                'toastType' => 'success',
            ]);
        } catch (\Exception $e) {
            return $this->render('components/follow-button.html.twig', [
                'author' => $author,
                'isFollowing' => false,
                'toastMessage' => 'Failed to follow user',
                'toastType' => 'error',
            ]);
        }
    }
}
