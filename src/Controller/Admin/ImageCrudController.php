<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions};
use EasyCorp\Bundle\EasyAdminBundle\Field\{TextareaField, NumberField};

final class ImageCrudController extends MediaCrudController
{
    public static function getEntityFqcn(): string { return Image::class; }
    public function configureActions(Actions $actions): Actions { return $actions->disable(Action::NEW, Action::DELETE); }
    public function configureFields(string $pageName): iterable
    {
        yield from parent::configureFields($pageName);
        yield TextareaField::new('description')->hideOnIndex();
        yield TextareaField::new('ocrText')->hideOnIndex();
        yield NumberField::new('latitude')->hideOnIndex()->hideOnForm();
        yield NumberField::new('longitude')->hideOnIndex()->hideOnForm();
    }
}
