<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Entity\ForumPost;
use App\Entity\NewsComment;
use App\Entity\NewsPost;
use App\Entity\Team;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(NewsPostCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('MJV Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::section('Contenu');
        yield MenuItem::linkToCrud('Actualites', 'fa fa-newspaper', NewsPost::class);
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comments', NewsComment::class);
        yield MenuItem::section('Communaute');
        yield MenuItem::linkToCrud('Forum', 'fa fa-forum', ForumPost::class);
        yield MenuItem::linkToCrud('Contacts', 'fa fa-envelope', Contact::class);
        yield MenuItem::section('Club');
        yield MenuItem::linkToCrud('Equipes', 'fa fa-users', Team::class);
        yield MenuItem::section('');
        yield MenuItem::linkToLogout('Deconnexion', 'fa fa-sign-out');
    }
}
