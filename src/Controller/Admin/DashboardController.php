<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Contact;
use App\Entity\Post;
use App\Entity\Product;
use App\Entity\Rubrik;
use App\Entity\User;
use App\Form\MenuUploadType;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\File;




//NB : Quand on créé un controller, automatiquement le système nous créé une vue, SAUF pour le dashboard (on doit la créer soit même)
class DashboardController extends AbstractDashboardController
{
    
    
    //0.0 Déclaration d'une variable que l'on va mettre en "protected" 
        //(elle deviendra un alias de la classe que je déclarerais en paramètre de ma méthode)
    protected $userRepository; 

    //0. MISE EN PLACE DU CONSTRUCTEUR (car c'est un user qui interagit avec le dashboard)

    public function __construct(UserRepository $userRepository) 
    //en paramètre, lui passer le "UserRepository" et en alias "$userRepository"
    {
        $this->userRepository = $userRepository;
    }

    
    //METHODE POUR LA GESTION DE LA PAGE D'ACCUEIL DU DASHBOARD
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        //return parent::index();


        //1. DEFINIR LE ROLE MINIMUM A AVOIR POUR ACCEDER AU DASHBOARD

            if ($this->isGranted('ROLE_EDITOR')){//si l'utilisateur à (au minimum le rôle "éditor")
            //"this" = fichier courant = > si on est ici dans le Dashboard, pour rentrer, il faut que l'utilisateur montre qu'il est...au moins éditeur
            //NB : on a déjà mis "isGranted" dans le PostController   
                return $this->render('admin/dashboard.html.twig'); //
            }else //sinon
            return $this->redirectToroute('app_main'); //retourner à la page d'accueil (route de l'index : 'app_post')
    }


    // Partie CENTRALE de la PAGE D'ACCUEIL du Dashboard ?
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('BABIMEAL - Administration'); //modif. avec le nom de mon application
    }


    //Mise en place de la PARTIE GAUCHE du Dashboard: l'ARBORESCENCE (le menu)
    public function configureMenuItems(): iterable 
    {
        //2. //Mise en place d'un lien sur mon Dashboard, qui va me permettre de retourner directement à mon site,
            // sans avoir à me déconnecter, puis me réconnecter.
            
        //yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Retourner au site','fa-solid fa-arrow-rotate-left','app_main');

       

        //if($this->isGranted('ROLE_ADMIN')){
        //    yield MenuItem::linkToDashboard('Dashboard','fa fa-home')->setPermission('ROLE_SUPER_ADMIN');
        // voir si garder ce block ?

        

        //3. METTRE EN PLACE LES DIFFERENTES TABLES 

        //Ici, les conditions seront toujours sur les rôles => discriminations

        if($this->isGranted('ROLE_EDITOR')){ //si tu as le rôle "editor", tu peux rentrer ici
            yield MenuItem::section('Posts');
            yield MenuItem::submenu('Posts','fa-sharp fa-solid fa-blog')->setSubItems([
                MenuItem::linkToCrud('Create Post','fas fa-newspaper', Post::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Show Post','fas fa-eye', Post::class), //ça doit nous venir de notre entité "post" donc "Post::class"
                //NB : pour charger une classe (dans "Use") = click droit ->Import class OU télécharger une extension pour
            ]);
        }

        if($this->isGranted('ROLE_MODO')){
            yield MenuItem::section('Comments'); //le modérateur va accéder à la table des commentaires
            yield MenuItem::submenu('Comments','fa fa-comment-dots')->setSubItems([
                MenuItem::linkToCrud('Create Comment','fas fa-newspaper', Comment::class)->setAction(Crud::PAGE_NEW),
                //A VOIR pour modifier car le modérateur n'est pas sensé créer des commentaires
                MenuItem::linkToCrud('Show Comment','fas fa-eye', Comment::class),
            ]);
        }

        if($this->isGranted('ROLE_ADMIN')){

            yield MenuItem::section('Rubrik');
            yield MenuItem::submenu('Rubriks','fa-solid-book-open-reader')->setSubItems([
                MenuItem::linkToCrud('Create Rubrik','fas fa-newspaper', Rubrik::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Show Rubrik','fas fa-eye', Rubrik::class),  
                ]);
            
            yield MenuItem::section('Product');
            yield MenuItem::submenu('Products','fa-solid-book-open-reader')->setSubItems([
                MenuItem::linkToCrud('Create Product','fas fa-newspaper', Product::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Show Product','fas fa-eye', Product::class),  
                ]);

            yield MenuItem::section('Category');
            yield MenuItem::submenu('Categories','fa-solid-book-open-reader')->setSubItems([
                MenuItem::linkToCrud('Create Category','fas fa-newspaper', Category::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Show Category','fas fa-eye', Category::class),  
                ]);
            
            yield MenuItem::section('Contact');
            yield MenuItem::submenu('Contacts','fa-solid-book-open-reader')->setSubItems([
                MenuItem::linkToCrud('Show Contact','fas fa-eye', Contact::class),
                ]);

            yield MenuItem::section('Menu');
            yield MenuItem::linkToRoute('Uploader la Carte', 'fa-solid fa-upload', 'admin_upload_card');
        
        }

        if($this->isGranted('ROLE_SUPER_ADMIN')){
            yield MenuItem::section('User');
            yield MenuItem::submenu('User','fa-user-circle')->setSubItems([
                MenuItem::linkToCrud('Create User','fa fa-plus-circle', User::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Show User','fas fa-eye', User::class),
                ]);
        }

      // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }




    
    public function configureUserMenu(UserInterface $user) : UserMenu 
    {
        if(!$user instanceof User){
            throw new \Exception('Wrong user');
        }
        $avatar = 'divers/avatars/' . $user->getAvatar();

        return parent::configureUserMenu($user)
            ->setAvatarUrl($avatar);
    }

  
}