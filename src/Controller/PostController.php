<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    private $repo;
    private $emi;

    public function __construct(PostRepository $repo, EntityManagerInterface $emi)
    {
        $this->repo = $repo;
        $this->emi = $emi;
    }

    // Affichage de la liste des posts et gestion des commentaires
    #[Route('/post', name: 'app_post')]
    public function index(Request $request, PostRepository $postRepository, CommentRepository $crepo, PaginatorInterface $paginator): Response
    {
        // Récupération des articles
        $queryBuilder = $postRepository->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            3
        );

        // Gestion du formulaire de commentaire
        $commentForm = [];

        // Traitement du formulaire de commentaire pour chaque post
        if ($this->isGranted('ROLE_USER')) {
            foreach ($pagination->getItems() as $post) {
                $comments = new Comment();
                $form = $this->createForm(CommentType::class, $comments);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $user = $this->getUser();
                    $comments->setUser($user);
                    $comments->setPost($post);
                    $comments->setCreatedAt(new \DateTimeImmutable());

                    // Persister le commentaire
                    $this->emi->persist($comments);
                    $this->emi->flush();

                    // Rediriger pour éviter la resoumission du formulaire
                    return $this->redirectToRoute('app_post');
                }

                $commentForm[$post->getId()] = $form->createView();
            }
        }

        return $this->render('post/index.html.twig', [
            'pagination' => $pagination,
            'comment_forms' => $commentForm,  // Passage des formulaires de commentaire à la vue
        ]);
    }
}
