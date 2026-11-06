<?php

namespace App\Controller\Admin;

use App\Entity\Interest;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Interest>
 */
final class InterestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Interest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['position' => 'ASC', 'id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextField::new('description');
        yield IntegerField::new('position')->setHelp('Lowest first.');
    }
}
