<?php

namespace App\Controller\Admin;

use App\Entity\Language;
use App\Enum\LanguageLevel;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Language>
 */
final class LanguageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Language::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['position' => 'ASC', 'id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield ChoiceField::new('level')
            ->setFormTypeOption('choice_label', static fn (LanguageLevel $level): string => $level->label())
            ->renderAsNativeWidget()
            ->formatValue(static fn (?LanguageLevel $level): ?string => $level?->label());
        yield IntegerField::new('position')->setHelp('Lowest first.');
    }
}
