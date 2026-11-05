<?php

namespace App\Tests\Controller\Admin;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
{
    public function testRightCredentialsLogIn(): void
    {
        $client = self::createAdminClient();

        self::logIn($client, 'test-password');

        self::assertResponseRedirects('/admin');
        $cookies = $client->getResponse()->headers->getCookies();
        self::assertCount(1, $cookies);
        self::assertTrue($cookies[0]->isSecure());
        self::assertTrue($cookies[0]->isHttpOnly());
        self::assertLoggedIn($client);
    }

    public function testRepeatedFailuresAreThrottled(): void
    {
        $client = self::createAdminClient();

        for ($attempt = 1; $attempt <= 5; ++$attempt) {
            self::logIn($client, 'wrong-password');
        }

        self::logIn($client, 'test-password');

        $client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Too many failed login attempts');
        self::assertNotLoggedIn($client);
    }

    public function testLoginFromAnotherOriginIsRefused(): void
    {
        $client = self::createAdminClient();

        $client->request(
            'POST',
            '/admin/login',
            ['_username' => 'admin', '_password' => 'test-password', '_csrf_token' => 'csrf-token'],
            [],
            ['HTTP_ORIGIN' => 'https://evil.example'],
        );

        $client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Invalid CSRF token.');
        self::assertNotLoggedIn($client);
    }

    private static function createAdminClient(): KernelBrowser
    {
        $client = self::createClient([], ['HTTPS' => 'on']);

        // Failed attempts live in the file cache, which survives between tests and between runs.
        $rateLimiterCache = self::getContainer()->get('cache.rate_limiter');
        self::assertInstanceOf(CacheItemPoolInterface::class, $rateLimiterCache);
        $rateLimiterCache->clear();

        return $client;
    }

    private static function logIn(KernelBrowser $client, string $password): void
    {
        $client->request('GET', '/admin/login');
        $client->submitForm('Sign in', ['_username' => 'admin', '_password' => $password]);
    }

    private static function assertLoggedIn(KernelBrowser $client): void
    {
        $client->request('GET', '/admin/skill');
        self::assertResponseIsSuccessful();
    }

    private static function assertNotLoggedIn(KernelBrowser $client): void
    {
        $client->request('GET', '/admin/skill');
        self::assertResponseRedirects('/admin/login');
    }
}
