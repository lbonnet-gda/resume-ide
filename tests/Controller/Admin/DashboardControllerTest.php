<?php

namespace App\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class DashboardControllerTest extends WebTestCase
{
    public function testAnonymousVisitorIsRedirectedToTheLoginForm(): void
    {
        $client = self::createClient([], ['HTTPS' => 'on']);

        $client->request('GET', '/admin');

        self::assertResponseRedirects('/admin/login');
        $client->followRedirect();
        self::assertSelectorExists('input[name="_password"]');
    }

    public function testAdminReachesTheDashboard(): void
    {
        $client = self::createClient([], ['HTTPS' => 'on']);
        $provider = self::getContainer()->get('security.user.provider.concrete.admin');
        self::assertInstanceOf(UserProviderInterface::class, $provider);
        $client->loginUser($provider->loadUserByIdentifier('admin'), 'admin');

        $client->request('GET', '/admin');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.ea-sidebar-item-link[href$="/admin/skill"]', 'Skills');
    }
}
