<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Rubrik;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\RubrikRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class MainController extends AbstractController
{
    private $repo;
    private $emi;

    public function __construct(PostRepository $repo, EntityManagerInterface $emi){
        $this->repo=$repo;
        $this->emi=$emi;
    }

    //Gestion de l'affichage de la page d'accueil (index)
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        //Afficher 1 article sur la page d'accueil (col-md-7)
        $posts = $this->repo->findBy([], ['createdAt' => 'DESC'], 1);

        //Afficher 4 articles sur la page d'accueil (col-md-3)
        $posts2 = $this->repo->findBy([], ['createdAt' => 'DESC'], 4, 2);

        //Afficher 2 articles sur la page d'accueil à gauche (col-md-6)
        $posts3 = $this->repo->findBy([], ['createdAt' => 'DESC'], 2, 6);

        //Afficher 2 article sur la page d'accueil à droite (col-md-6)
        $posts4 = $this->repo->findBy([], ['createdAt' => 'DESC'], 2);

        return $this->render('main/index.html.twig', [


        ]);
    }
}