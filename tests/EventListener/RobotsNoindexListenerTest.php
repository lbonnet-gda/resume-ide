<?php

namespace App\Tests\EventListener;

use App\EventListener\RobotsNoindexListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class RobotsNoindexListenerTest extends TestCase
{
    public function testDisabledListenerLeavesTheResponseIndexable(): void
    {
        $response = new Response();
        $kernel = $this->createStub(HttpKernelInterface::class);

        new RobotsNoindexListener(false)(new ResponseEvent($kernel, new Request(), HttpKernelInterface::MAIN_REQUEST, $response));

        self::assertFalse($response->headers->has('X-Robots-Tag'));
    }
}
