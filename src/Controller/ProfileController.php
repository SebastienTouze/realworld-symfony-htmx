<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository) {}

    #[Route('/profile/{username}', name: 'app_profile')]
    public function index(string $username): Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}
