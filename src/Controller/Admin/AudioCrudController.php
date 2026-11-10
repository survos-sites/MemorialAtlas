<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Audio;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions};
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

final class AudioCrudController extends MediaCrudController
{
    public static function getEntityFqcn(): string { return Audio::class; }
    public function configureActions(Actions $actions): Actions { return $actions->disable(Action::NEW, Action::DELETE); }
    public function configureFields(string $pageName): iterable
    {
        yield from parent::configureFields($pageName);
        yield TextareaField::new('transcript')->hideOnIndex();
    }
}
