<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Item;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField, TextareaField, DateTimeField, AssociationField};

final class ItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Item::class; }
    public function configureCrud(Crud $crud): Crud { return $crud->setDefaultSort(['id' => 'DESC']); }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title');
        yield TextField::new('marking')->hideOnForm();
        yield TextField::new('recordType')->hideOnForm();
        yield AssociationField::new('cemetery')->setRequired(false);
        yield TextareaField::new('locationContext');
        yield TextareaField::new('description');
        yield AssociationField::new('media')->hideOnForm();
        yield DateTimeField::new('createdAt')->hideOnForm();
    }
}
