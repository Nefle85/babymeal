<?php

namespace App\Controller\Admin;

use App\Entity\Rubrik;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RubrikCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Rubrik::class;
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
            ->setPageTitle(Crud::PAGE_INDEX, 'Rubriques') // Changer le titre de la page
            ->setEntityLabelInPlural('Rubriques') // Label pluriel
            ->setEntityLabelInSingular('Rubrique'); // Label singulier
  
    }
  
}
