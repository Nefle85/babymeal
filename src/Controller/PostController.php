<?php

// src/Controller/ArticleController.php
namespace App\Controller;

use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface; // Utilisation correcte de l'interface PaginatorInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(Request $request, PostRepository $postRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $postRepository->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1), // Page number
            5 // Number of items per page
        );

        return $this->render('post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
