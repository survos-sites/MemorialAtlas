<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Media;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Action, Actions, Crud};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField, TextareaField, IntegerField, AssociationField};

class MediaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Media::class; }
    public function configureActions(Actions $actions): Actions { return $actions->disable(Action::NEW, Action::DELETE, Action::EDIT); }
    public function configureCrud(Crud $crud): Crud { return $crud->setDefaultSort(['id' => 'DESC']); }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('item')->hideOnForm();
        yield IntegerField::new('position')->hideOnForm();
        yield TextField::new('originalName', 'Preview')->setTemplatePath('admin/field/media_preview.html.twig')->hideOnForm();
        yield TextField::new('kind')->hideOnForm()->setSortable(false);
        yield TextField::new('marking')->hideOnForm();
        yield TextField::new('source');
        yield TextField::new('donor');
    }
}
