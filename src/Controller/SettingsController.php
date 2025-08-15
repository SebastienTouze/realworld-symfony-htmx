<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SettingsType;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_settings', methods: ['GET', 'POST'])]
    public function index(#[CurrentUser] User $user, Request $request, UserService $userService): Response
    {
        $old_username = $user->getUsername();

        $form = $this->createForm(SettingsType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $userService->saveUser($user);
            } else {
                // this is to avoid the case when the user set empty username
                $user->setUsername($old_username);
            }
        }

        return $this->render('settings/index.html.twig', [
            'form' => $form,
        ]);
    }
}
