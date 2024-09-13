<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    #[Route('/menu', name: 'app_menu')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        // Récupère toutes les catégories avec leurs produits associés
        $categories = $categoryRepository->findAll();

        // Rendu de la vue avec les catégories et les produits
        return $this->render('menu/index.html.twig', [
            'categories' => $categories,
        ]);
    }
}