<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SettingsType;
use App\Service\UserService;
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

        $toastMessage = null;
        $toastType = null;

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $userService->saveUser($user);
                $toastMessage = 'Settings updated successfully!';
                $toastType = 'success';
            } else {
                // this is to avoid the case when the user set empty username
                $user->setUsername($old_username);
                $toastMessage = 'Failed to update settings';
                $toastType = 'error';
            }
        }

        $isHTMXRequest = $request->headers->get('HX-Request', false);
        if ($isHTMXRequest) {
            return $this->render('settings/components/settings-form-partial.html.twig',
                [
                    'form' => $form,
                    'toastMessage' => $toastMessage,
                    'toastType' => $toastType,
                ]);
        }

        return $this->render('settings/index.html.twig', ['form' => $form]);
    }
}
