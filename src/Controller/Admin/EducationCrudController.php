<?php

namespace App\Controller\Admin;

use App\Entity\Education;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Education>
 */
final class EducationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Education::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['startDate' => 'DESC', 'id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('school');
        yield TextField::new('degree');
        yield TextField::new('location')->hideOnIndex();
        yield DateField::new('startDate');
        yield DateField::new('endDate')->setHelp('Leave empty for a current degree.');
        yield TextareaField::new('description')->hideOnIndex();
    }
}
