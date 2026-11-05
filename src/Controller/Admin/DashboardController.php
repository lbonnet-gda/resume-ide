<?php

namespace App\Controller\Admin;

use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function index(): Response
    {
        $profileId = $this->profileId();

        if ($profileId === null) {
            return $this->redirectToRoute('admin_profile_new');
        }

        return $this->redirectToRoute('admin_profile_edit', ['entityId' => $profileId]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('resume-ide');
    }

    public function configureMenuItems(): iterable
    {
        $profileId = $this->profileId();
        $profile = MenuItem::linkTo(ProfileCrudController::class, 'Profile', 'fa fa-user');

        if ($profileId === null) {
            yield $profile->setAction(Action::NEW);
        } else {
            yield $profile->setAction(Action::EDIT)->setEntityId($profileId);
        }

        yield MenuItem::linkTo(ExperienceCrudController::class, 'Experiences', 'fa fa-briefcase');
        yield MenuItem::linkTo(EducationCrudController::class, 'Education', 'fa fa-graduation-cap');
        yield MenuItem::linkTo(SkillCrudController::class, 'Skills', 'fa fa-gears');
        yield MenuItem::linkTo(ProjectCrudController::class, 'Projects', 'fa fa-diagram-project');
        yield MenuItem::linkTo(LanguageCrudController::class, 'Languages', 'fa fa-language');
        yield MenuItem::linkTo(InterestCrudController::class, 'Interests', 'fa fa-heart');
    }

    private function profileId(): ?int
    {
        return $this->entityManager->getRepository(Profile::class)->findOneBy([], ['id' => 'ASC'])?->getId();
    }
}
