<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Используется для вывода демо страницы "Форма контактов"
 */
class ContactsController extends AbstractController
{
    #[Route('/contacts')]
    public function index(): Response
    {
        return $this->render('contacts/index.html.twig');
    }
}
