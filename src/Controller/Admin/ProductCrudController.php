<?php

namespace App\Controller\Admin;

use App\Entity\Category; // Assure-toi d'importer la bonne entité pour la relation Category
use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField; // Utilise DateTimeField pour createdAt et updatedAt
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('id')->onlyOnIndex(),

            TextField::new('name','Nom')->setColumns('col-md-6'),

            TextField::new('details','Détails')->setColumns('col-md-6'),

            TextField::new('description')->setColumns('col-md-6'),

            AssociationField::new('category','Catégorie')->setColumns('col-md-3'),

            NumberField::new('price','Prix')->setColumns('col-md-3'), //Utilisation d'un champ de type NumberField car l'attribut "price" est de type décimal (numérique)

            $image = ImageField::new('image')
                ->setUploadDir('public/divers/images')
                ->setBasePath('divers/images')
                ->setSortable(false)
                ->setFormTypeOption('required', false)
                ->setColumns('col-md-2'),

            BooleanField::new('available')
            ->setColumns('col-md-2 mt-4')
            ->setLabel('Disponible'),
            
            DateTimeField::new('createdAt','Créé le')->onlyOnIndex(),

            DateTimeField::new('updatedAt','Modifié le')->onlyOnIndex(),

            // Note: 'createdBy' n'est pas inclus ici car il est souvent automatiquement géré
            // avec la relation utilisateur. Tu peux l'ajouter si nécessaire avec des permissions spécifiques.

        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Plats') // Changer le titre de la page
            ->setEntityLabelInSingular('Plat') 
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(5)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('category')
            ->add('available')
            // Note: Si tu veux filtrer par dates, tu peux ajouter 'createdAt' et 'updatedAt'
    ;

    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }
}
