<?php

namespace App\Controller\htmx;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('htmx/author')]
class FollowButtonController extends AbstractController
{
    #[Route('/{username}/follow', name: 'app_htmx_followbutton_followuser', methods: ['POST'])]
    public function followUser(?User $author, #[CurrentUser] ?User $currentUser): Response|NotFoundHttpException
    {
        if(null === $currentUser) {
            return $this->redirectToRoute('app_login');
        }

        if(null === $author) {
            return $this->createNotFoundException("No author for this username.");
        }

        return $this->render('components/follow-button.html.twig', [
            'author' => $author,
        ]);
    }
}
