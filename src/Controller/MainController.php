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
            'posts' => $posts,
            'posts2' => $posts2,
            'posts3' => $posts3,
            'posts4' => $posts4,


        ]);
    }

//GESTION DE LA RECUPERATION DU DETAIL D'UN POST(TOTALITE D'UN POST)
#[IsGranted('ROLE_USER')]
#[Route('/post/{id}', name: 'show', requirements: ['id' =>'\d+'])]

public function showone(Post $post, Request $req, $id, PostRepository $reppo, EntityManagerInterface $emi, CommentRepository $crepo): Response
{
    // Vérification du post
    if (!$post) {
        return $this->redirectToRoute('app_main');
    }

    $comments = new Comment();
    $posts = $reppo->find($id);

    // Créer le formulaire
    $commentForm = $this->createForm(CommentType::class,$comments);
    $commentForm->handleRequest($req);

    // Traitement du formulaire de commentaire
    if ($commentForm->isSubmitted() && $commentForm->isValid()) {
        $user = $this->getUser();
        $comments->setUser($user);
        $comments->setPost($posts);
        $comments->setCreatedAt(new \DateTimeImmutable());

    // Persister le commentaire
    $emi->persist($comments);
    $emi->flush();

    // Rediriger pour éviter la resoumission du formulaire
    return $this->redirectToRoute('show', ['id' => $id]);
    }

    // Récupération des commentaires pour le post
    $allComments = $crepo->findByPostOrderedByCreatedAtDesc($id);

    // Rendre la vue avec les données appropriées
    return $this->render('show/show.html.twig', [
        'post' => $post,
        'comments' => $allComments,
        'comment_form' => $commentForm->createView(),
    ]);
}


    //GESTION DES RUBRIKS
    #[Route('/rubrik/rubrik/{id}', name: 'posts_by_rubrik')]
    public function postsByRubrik(Rubrik $rubrik, $id, RubrikRepository $rubrikRepository, PostRepository $postRepository): Response
    {
        $rubrik = $rubrikRepository->find($id);
        if (!$rubrik) {
            throw $this->createNotFoundException('Rubrik not found');
        }

        // Obtenez les articles de Rubrik classés par date de création DESC
        $posts = $postRepository->findBy(['rubrik' => $rubrik],['createdAt' => 'DESC']);
        return $this->render('rubrik/rubrik.html.twig', [
            'rubrik' => $rubrik,
            'posts' => $posts,
        ]);
    }
}
