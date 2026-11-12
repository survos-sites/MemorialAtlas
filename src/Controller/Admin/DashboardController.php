<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\{Item, Media};
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Dashboard, MenuItem};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirect($this->container->get(AdminUrlGenerator::class)->setController(ItemCrudController::class)->generateUrl());
    }
    public function configureDashboard(): Dashboard { return Dashboard::new()->setTitle('MemorialAtlas'); }
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Search photographs', 'fa fa-search', 'image_search');
        yield MenuItem::linkToRoute('Home', 'fa fa-home', 'home');
        yield MenuItem::linkTo(CemeteryCrudController::class, 'Cemeteries', 'fa fa-map-marker-alt');
        yield MenuItem::linkTo(ItemCrudController::class, 'Memorial records', 'fa fa-landmark');
        yield MenuItem::linkTo(MediaCrudController::class, 'All media', 'fa fa-photo-film');
        yield MenuItem::linkTo(ImageCrudController::class, 'Photographs', 'fa fa-images');
        yield MenuItem::linkTo(AudioCrudController::class, 'Audio notes', 'fa fa-microphone');
    }
}
