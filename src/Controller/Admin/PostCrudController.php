<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;

class PostCrudController extends AbstractCrudController
{
    public function __construct(private Security $security) {} 

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function createEntity(string $entityFqcn): Post
    {
        return new Post();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Post) {
            return;
        }

        $user = $this->security->getUser();
        $entityInstance->setUser($user);
        
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    public function configureFields(string $pageName): iterable
    {
        $user = $this->security->getUser();

        return [
            IntegerField::new('id')->onlyOnIndex(),
            TextField::new('title', 'Titre')->setColumns('col-md-6'),
            TextField::new('abstract', 'Sous-titre')->setColumns('col-md-6'),
            TextField::new('content', 'Contenu')->setColumns('col-md-6'),
            ImageField::new('image')
                ->setUploadDir('public/divers/images')
                ->setBasePath('divers/images')
                ->setSortable(false)
                ->setFormTypeOption('required', false)->setColumns('col-md-2'),
            AssociationField::new('rubrik', 'Rubrique')->setColumns('col-md-4'),
            TextField::new('user', 'Auteur')
                ->setDisabled()
                ->setValue($user instanceof User ? $user->getFullName() : '') // Vérification ici
                ->setColumns('col-md-6'),
            DateField::new('createdAt', 'Créé le')->onlyOnIndex(),
            BooleanField::new('isPublished')->setPermission('ROLE_ADMIN')->setColumns('col-md-1')->setLabel('Publié'),
        ];
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Articles') // Changer le titre de la page
            ->setEntityLabelInSingular('Article') 
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(5);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('user')
            ->add('title')
            ->add('rubrik');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }
}
