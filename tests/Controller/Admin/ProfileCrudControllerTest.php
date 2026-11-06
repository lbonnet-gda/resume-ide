<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class ProfileCrudControllerTest extends WebTestCase
{
    public function testOnlyOneProfileCanExist(): void
    {
        $client = self::createClient([], ['HTTPS' => 'on']);
        $provider = self::getContainer()->get('security.user.provider.concrete.admin');
        self::assertInstanceOf(UserProviderInterface::class, $provider);
        $client->loginUser($provider->loadUserByIdentifier('admin'), 'admin');
        self::entityManager()->createQuery('DELETE FROM '.Profile::class)->execute();

        $client->request('GET', '/admin');
        self::assertResponseRedirects('/admin/profile/new');
        $client->followRedirect();

        $client->submitForm('Create');
        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, self::entityManager()->getRepository(Profile::class)->count());

        $client->submitForm('Create', [
            'Profile[name]' => 'Jane Doe',
            'Profile[title]' => 'Développeuse',
            'Profile[summary]' => 'Résumé.',
            'Profile[email]' => 'jane.doe@example.com',
            'Profile[phone]' => '+33 6 39 98 00 00',
            'Profile[location]' => 'Lyon, France',
        ]);
        self::assertResponseRedirects();
        $profile = self::entityManager()->getRepository(Profile::class)->findOneBy([]);
        self::assertSame('Jane Doe', $profile?->getName());

        $client->request('GET', '/admin/profile/new');
        self::assertResponseStatusCodeSame(403);

        $client->request('POST', sprintf('/admin/profile/%d/delete', $profile->getId()));
        self::assertResponseStatusCodeSame(403);
        self::assertSame(1, self::entityManager()->getRepository(Profile::class)->count());

        $client->request('GET', '/admin');
        self::assertResponseRedirects(sprintf('/admin/profile/%d/edit', $profile->getId()));
    }

    private static function entityManager(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
