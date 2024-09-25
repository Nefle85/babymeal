<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Contact;
use App\Entity\Post;
use App\Entity\Product;
use App\Entity\Rubrik;
use App\Entity\User;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    protected $userRepository; 

    public function __construct(UserRepository $userRepository) 
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_EDITOR')) {
            return $this->render('admin/dashboard.html.twig', [
                'custom_styles' => $this->getCustomStyles(),
            ]);
        } else {
            return $this->redirectToRoute('app_main');
        }
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('BABIMEAL - Administration');
    }

    public function configureMenuItems(): iterable 
    {
        yield MenuItem::linkToRoute('Retourner au site', 'fa-solid fa-arrow-rotate-left', 'app_main');

        if ($this->isGranted('ROLE_EDITOR')) {
            yield MenuItem::section('Articles de la section "actu"');
            yield MenuItem::linkToCrud('Ecrire un nouvel article', 'fas fa-newspaper', Post::class)->setAction(Crud::PAGE_NEW);
            yield MenuItem::linkToCrud('Voir les articles', 'fas fa-eye', Post::class);
        }

        if ($this->isGranted('ROLE_MODO')) {
            yield MenuItem::section('Commentaires aux articles de la section "actu"');
            yield MenuItem::linkToCrud('Ecrire un commentaire', 'fas fa-newspaper', Comment::class)->setAction(Crud::PAGE_NEW);
            yield MenuItem::linkToCrud('Voir les commentaires', 'fas fa-eye', Comment::class);
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Rubriques de la section "actu"');
            yield MenuItem::linkToCrud('Ajouter une rubrique', 'fas fa-newspaper', Rubrik::class)->setAction(Crud::PAGE_NEW);
            yield MenuItem::linkToCrud('Voir les rubriques existantes', 'fas fa-eye', Rubrik::class);
            
            yield MenuItem::section('Plats de la carte');
            yield MenuItem::linkToCrud('Ajouter un plat', 'fas fa-newspaper', Product::class)->setAction(Crud::PAGE_NEW);
            yield MenuItem::linkToCrud('Voir les plats existants', 'fas fa-eye', Product::class);
            
            yield MenuItem::section('Catégories de plats');
            yield MenuItem::linkToCrud('Ajouter une catégorie', 'fas fa-newspaper', Category::class)->setAction(Crud::PAGE_NEW);
            yield MenuItem::linkToCrud('Voir les catégories existantes', 'fas fa-eye', Category::class);
            
            yield MenuItem::section('Contact');
            yield MenuItem::linkToCrud('Voir les messages', 'fas fa-eye', Contact::class);
            
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Utilisateurs');
            yield MenuItem::linkToCrud('Voir les utilisateurs', 'fas fa-eye', User::class);
        }
    }

    public function configureUserMenu(UserInterface $user): UserMenu 
    {
        if (!$user instanceof User) {
            throw new \Exception('Wrong user');
        }
        $avatar = 'divers/avatars/' . $user->getAvatar();

        return parent::configureUserMenu($user)
            ->setAvatarUrl($avatar);
    }

    // Méthode pour retourner le CSS directement
    public function getCustomStyles(): string
    {
        return <<<CSS
        <style>
            /* Styles pour les éléments du menu */
            .navbar-nav .nav-item {
                font-weight: bold;
                color: orange; /* Couleur orange pour les menus */
            }
            /* Personnaliser d'autres éléments selon vos besoins */
        </style>
        CSS;
    }
}
