<?php

namespace App\Controller\Admin;

use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<Profile>
 */
final class ProfileCrudController extends AbstractCrudController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Profile::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->disable(Action::DELETE, Action::BATCH_DELETE, Action::SAVE_AND_ADD_ANOTHER);

        if ($this->entityManager->getRepository(Profile::class)->count() > 0) {
            $actions->disable(Action::NEW);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextField::new('title');
        yield TextareaField::new('summary')->hideOnIndex();
        yield EmailField::new('email');
        yield TelephoneField::new('phone')->setHelp('Only shown in the admin and in the admin PDF.');
        yield TextField::new('location');
        yield UrlField::new('linkedinUrl', 'LinkedIn URL')->hideOnIndex();
        yield UrlField::new('githubUrl', 'GitHub URL')->hideOnIndex();
    }
}
