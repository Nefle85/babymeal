<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Form\ContactResponseType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('lastname', 'Nom');
        yield TextField::new('firstname', 'Prénom');
        yield EmailField::new('email', 'Email')
            ->setFormTypeOptions(['attr' => ['style' => 'color: orange;']]); // Couleur orange
        yield EmailField::new('subject', 'Sujet')
            ->setFormTypeOptions(['attr' => ['style' => 'color: orange;']]); // Couleur orange
        yield TextareaField::new('message', 'Message')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Date d\'envoi')->hideOnForm();
        yield TextareaField::new('response', 'Réponse')->hideOnIndex();
        yield BooleanField::new('isResponded', 'Répondu')
            ->renderAsSwitch(false)
            ->setFormTypeOptions(['attr' => ['style' => 'color: orange;']]); // Couleur orange
    }

    public function configureActions(Actions $actions): Actions
    {
        $respond = Action::new('respond', 'Répondre', 'fa fa-reply')
            ->linkToCrudAction('respondToContact')
            ->setCssClass('btn-primary');

        // Configurez l'action de suppression
        $delete = Action::new('delete', 'Supprimer', 'fa fa-trash')
            ->setCssClass('btn-warning text-white') // Style du bouton
            ->setHtmlAttributes([
                'data-confirm' => 'Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.', // Message de confirmation
            ]);

        return $actions
            ->add(Crud::PAGE_INDEX, $respond)
            ->add(Crud::PAGE_DETAIL, $respond)
            // Met à jour l'action "delete" existante pour la page d'index
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setCssClass('btn-warning text-white') // Applique le style orange
                    ->setHtmlAttributes([
                        'data-confirm' => 'Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.', // Message de confirmation
                    ]);
            })
            // Retrait des actions "add" et "edit"
            ->remove(Crud::PAGE_INDEX, Action::NEW)  // Retire le bouton "Ajouter"
            ->remove(Crud::PAGE_INDEX, Action::EDIT) // Retire le bouton "Éditer"
            ->setPermission(Action::DELETE, 'ROLE_ADMIN'); // Assurez-vous que le rôle est correctement défini
    }

    public function respondToContact(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contact = $this->getContext()->getEntity()->getInstance();
        $form = $this->createForm(ContactResponseType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setIsResponded(true);
            $entityManager->flush();

            $this->addFlash('success', 'La réponse a été envoyée avec succès.');

            return $this->redirect($this->generateUrl('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => self::class,
            ]));
        }

        return $this->render('admin/contact/respond.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }
}
