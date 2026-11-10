<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Cemetery;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField};

final class CemeteryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Cemetery::class; }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield TextField::new('locality')->setRequired(false);
    }
}
