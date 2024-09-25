<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name','Nom'),
        ];
    }

    public function configureCrud(Crud $crud): Crud // Utiliser la classe correcte
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Catégories de plats') // Changer le titre de la page
            ->setEntityLabelInPlural('Catégories') // Label pluriel
            ->setEntityLabelInSingular('Catégorie'); // Label singulier
  
    }

}
