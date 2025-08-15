<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_settings', methods: ['GET', 'POST'])]
    public function index(#[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $old_username = $user->getUsername();

        $form = $this->createForm(SettingsType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();
            } else {
                $user->setUsername($old_username);
            }
        }

        return $this->render('settings/index.html.twig', [
            'form' => $form,
        ]);
    }
}
