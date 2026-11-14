<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Cemetery;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField, NumberField, IntegerField};

final class CemeteryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Cemetery::class; }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield TextField::new('locality')->setRequired(false);
        yield NumberField::new('latitude')->setNumDecimals(7)->setRequired(false);
        yield NumberField::new('longitude')->setNumDecimals(7)->setRequired(false);
        yield IntegerField::new('matchingRadiusMeters')->setRequired(false)->setHelp('Optional GPS matching area around this center, in meters. Set all three fields to enable matching. Overlapping matches require an explicit cemetery selection.');
    }
}
