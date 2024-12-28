<?php

namespace App\Controller\htmx;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('htmx/tags')]
class TagListController extends AbstractController
{
    #[Route('/', name: 'app_tag_list')]
    public function list(): Response
    {
        return $this->render('components/tag-list.html.twig');
    }
}
