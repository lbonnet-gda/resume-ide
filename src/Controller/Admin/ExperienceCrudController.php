<?php

namespace App\Controller\Admin;

use App\Entity\Experience;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Experience>
 */
final class ExperienceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Experience::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['startDate' => 'DESC', 'id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('company');
        yield TextField::new('role');
        yield TextField::new('location')->hideOnIndex();
        yield DateField::new('startDate');
        yield DateField::new('endDate')->setHelp('Leave empty for a current job.');
        yield ArrayField::new('missions')->hideOnIndex();
        yield ArrayField::new('stack')->hideOnIndex();
    }
}
