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
        yield TextField::new('name', 'Nom');
        yield EmailField::new('email', 'Email');
        yield TextareaField::new('message', 'Message')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Date d\'envoi')->hideOnForm();
        yield TextareaField::new('response', 'Réponse')->hideOnIndex();
        yield BooleanField::new('isResponded', 'Répondu')->renderAsSwitch(false);
    }

    public function configureActions(Actions $actions): Actions
    {
        $respond = Action::new('respond', 'Répondre', 'fa fa-reply')
            ->linkToCrudAction('respondToContact');

        return $actions
            ->add(Crud::PAGE_INDEX, $respond)
            ->add(Crud::PAGE_DETAIL, $respond);
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